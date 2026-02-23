<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Filament\Resources\LeaveResource\RelationManagers;
use App\Models\Leave;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Action;
use App\Models\LeaveQuota;
use App\Models\Typeofleave;
use App\Models\Attendance;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;


    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        \Filament\Schemas\Components\Section::make('Detail Pengajuan')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pegawai')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->default(fn () => auth()->id())
                                    ->disabled(fn () => !auth()->user()->hasAnyRole(['super_admin', 'admin', 'kepala']))
                                    ->dehydrated()
                                    ->required()
                                    ->live(),

                                Forms\Components\Select::make('typeofleave_id')
                                    ->label('Jenis Cuti')
                                    ->relationship('typeofleave', 'leaves_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->live(),
                                    
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->afterOrEqual('start_date')
                                    ->live(),
                                    
                                Forms\Components\Placeholder::make('quota_info')
                                    ->label('Informasi Kuota')
                                    ->visible(function ($get) {
                                        $typeofleaveId = $get('typeofleave_id');
                                        if (!$typeofleaveId) return false;
                                        
                                        $type = Typeofleave::find($typeofleaveId);
                                        return $type && $type->has_quota;
                                    })
                                    ->content(function ($get) {
                                        $typeofleaveId = $get('typeofleave_id');
                                        $startDate = $get('start_date');
                                        
                                        if (!$typeofleaveId || !$startDate) return '-';
                                        
                                        $year = \Carbon\Carbon::parse($startDate)->year;
                                        
                                        // Use the selected user_id from the form if available
                                        $userId = $get('user_id');
                                        
                                        // If empty (e.g., initial creation load), fallback to auth or active record
                                        if (!$userId) {
                                            $recordId = $get('id');
                                            if ($recordId) {
                                                $record = \App\Models\Leave::find($recordId);
                                                $userId = $record ? $record->user_id : auth()->id();
                                            } else {
                                                $userId = auth()->id();
                                            }
                                        }
                                        
                                        $quota = LeaveQuota::where('user_id', $userId)
                                            ->where('typeofleave_id', $typeofleaveId)
                                            ->where('year', $year)
                                            ->first();
                                            
                                        if (!$quota) {
                                            return new \Illuminate\Support\HtmlString('<span class="text-danger-600 font-medium">Kuota belum diatur untuk tahun ini.</span>');
                                        }
                                        
                                        $remaining = $quota->remaining_days;
                                        $color = $remaining > 0 ? 'success' : 'danger';
                                        
                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-medium'>Sisa: {$remaining} hari (Total: {$quota->total_days} hari)</span>");
                                    }),
                                    
                                Forms\Components\Textarea::make('reason')
                                    ->label('Alasan')
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),
                            
                        \Filament\Schemas\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('attachment')
                                    ->label('Lampiran Dokumen')
                                    ->multiple()
                                    ->maxFiles(3)
                                    ->directory('leave-attachments')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(5120) // 5MB limit per file
                                    ->required(function ($get) {
                                        $type = Typeofleave::find($get('typeofleave_id'));
                                        return $type ? $type->requires_attachment : false;
                                    }),
                            ]),
                    ]),
                    
                \Filament\Schemas\Components\Group::make()
                    ->columnSpan(1)
                    ->schema([
                        \Filament\Schemas\Components\Section::make('Status Cuti')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    ])
                                    ->default('pending')
                                    ->required(),
                                    
                                Forms\Components\Textarea::make('note')
                                    ->label('Catatan Approval'),
                                    
                                Forms\Components\Select::make('approved_by_name')
                                    ->label(function ($record) {
                                        if (!$record) return 'Ditinjau Oleh';
                                        return $record->status === 'rejected' ? 'Ditolak Oleh' : 'Disetujui Oleh';
                                    })
                                    ->options(function () {
                                        // Pluck all users who have the 'kepala' role directly
                                        return \App\Models\User::role('kepala')->pluck('name', 'name');
                                    })
                                    ->searchable()
                                    ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                                    ->helperText(fn () => auth()->user()->hasRole('super_admin') ? 'Super Admin dapat mengubah nama penyetuju.' : ''),
                                    
                                Forms\Components\DateTimePicker::make('approved_at')
                                    ->label('Waktu Persetujuan')
                                    ->native(false)
                                    ->displayFormat('d M Y, H:i'),
                            ]),
                    ]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('typeofleave.leaves_name')
                    ->label('Jenis Cuti')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tgl Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days')
                    ->label('Jumlah')
                    ->state(fn (\App\Models\Leave $record): string => $record->leaveDays() . ' hari'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak'
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                
                // Approval Action
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (\App\Models\Leave $record) => $record->status === 'pending' && auth()->user()->hasAnyRole(['super_admin', 'kepala']))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan (Opsional)'),
                        Forms\Components\Select::make('approved_by_name')
                            ->label('Disetujui Oleh')
                            ->options(function () {
                                return \App\Models\User::role(['kepala', 'super_admin'])->pluck('name', 'name');
                            })
                            ->searchable()
                            ->default(fn () => auth()->user()->name)
                            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                    ])
                    ->action(function (\App\Models\Leave $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'note' => $data['note'] ?? null,
                            'approved_by_name' => auth()->user()->hasRole('super_admin') && !empty($data['approved_by_name']) ? $data['approved_by_name'] : auth()->user()->name,
                            'approved_at' => now(),
                        ]);
                        
                        // Explicitly deduct quota and mark attendance
                        \App\Models\Leave::deductQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                        \App\Models\Leave::markAttendance($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                        
                        Notification::make()
                            ->title('Cuti Disetujui')
                            ->success()
                            ->send();
                    }),
                    
                // Rejection Action
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (\App\Models\Leave $record) => $record->status === 'pending' && auth()->user()->hasAnyRole(['super_admin', 'kepala']))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Alasan Penolakan')
                            ->required(),
                        Forms\Components\Select::make('approved_by_name')
                            ->label('Ditolak Oleh')
                            ->options(function () {
                                return \App\Models\User::role(['kepala', 'super_admin'])->pluck('name', 'name');
                            })
                            ->searchable()
                            ->default(fn () => auth()->user()->name)
                            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                    ])
                    ->action(function (\App\Models\Leave $record, array $data) {
                        // If previously approved, refund quota first
                        if ($record->getOriginal('status') === 'approved') {
                            \App\Models\Leave::refundQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                            \App\Models\Leave::unmarkAttendance($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                        }
                        
                        $record->update([
                            'status' => 'rejected',
                            'note' => $data['note'],
                            'approved_by_name' => auth()->user()->hasRole('super_admin') && !empty($data['approved_by_name']) ? $data['approved_by_name'] : auth()->user()->name,
                            'approved_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Cuti Ditolak')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['super_admin']))
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if ($record->status === 'approved') {
                                    \App\Models\Leave::refundQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                                    \App\Models\Leave::unmarkAttendance($record->user_id, $record->start_date, $record->end_date);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\LeaveResource\Widgets\LeaveQuotaOverview::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Cuti";
        }
        else
        {
            return "Leaves";
        }
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Attendance Management';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Super admin and users with access to Leave Quotas can view all leaves
        if ($user && ($user->hasRole(['super_admin']) || $user->can('viewAny', \App\Models\LeaveQuota::class))) {
            return $query;
        } elseif ($user && $user->hasRole('kepala')) {
            // Team leader sees their members' leaves and their own
            $teamMemberIds = \Illuminate\Support\Facades\DB::table('team_user')
                ->join('teams', 'team_user.team_id', '=', 'teams.id')
                ->where('teams.user_id', $user->id)
                ->pluck('team_user.user_id')
                ->toArray();
                
            $teamMemberIds[] = $user->id; // Include themselves
            
            return $query->whereIn('user_id', array_unique($teamMemberIds));
        } else {
            return $query->where('user_id', $user ? $user->id : 0);
        }
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-m-minus-circle';
    }
}
