<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveQuotaResource\Pages;
use App\Models\LeaveQuota;
use App\Models\Typeofleave;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveQuotaResource extends Resource
{
    protected static ?string $model = LeaveQuota::class;

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('typeofleave_id')
                    ->label('Jenis Cuti')
                    ->options(Typeofleave::where('has_quota', true)->pluck('leaves_name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                        if ($state) {
                            $type = Typeofleave::find($state);
                            if ($type && $type->default_quota_days) {
                                $set('total_days', $type->default_quota_days);
                            }
                        }
                    }),
                    
                Forms\Components\TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),
                    
                Forms\Components\TextInput::make('total_days')
                    ->label('Total Kuota (Hari)')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                    
                Forms\Components\TextInput::make('used_days')
                    ->label('Kuota Terpakai')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('typeofleave.leaves_name')
                    ->label('Jenis Cuti')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_days')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('used_days')
                    ->label('Terpakai')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Sisa')
                    ->badge()
                    ->color(fn (LeaveQuota $record): string => $record->remaining_days > 0 ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = LeaveQuota::select('year')->distinct()->pluck('year', 'year')->toArray();
                        if (empty($years)) {
                            $currentYear = date('Y');
                            return [$currentYear => $currentYear];
                        }
                        return $years;
                    }),
                Tables\Filters\SelectFilter::make('typeofleave_id')
                    ->label('Jenis Cuti')
                    ->relationship('typeofleave', 'leaves_name'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                // Custom action to generate quotas for all users
                \Filament\Actions\Action::make('generate_yearly_quotas')
                    ->label('Generate Kuota Tahunan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Proses ini akan membuat kuota baru untuk semua pegawai aktif berdasarkan Jenis Cuti yang memiliki batas kuota. Proses ini aman dan akan mengabaikan pegawai yang sudah memiliki kuota di tahun tersebut.')
                    ->form([
                        Forms\Components\TextInput::make('target_year')
                            ->label('Tahun')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $year = $data['target_year'];
                        $users = User::all();
                        $quotaTypes = Typeofleave::where('has_quota', true)->whereNotNull('default_quota_days')->get();
                        
                        $count = 0;
                        foreach ($users as $user) {
                            foreach ($quotaTypes as $type) {
                                $exists = LeaveQuota::where('user_id', $user->id)
                                    ->where('typeofleave_id', $type->id)
                                    ->where('year', $year)
                                    ->exists();
                                    
                                if (!$exists) {
                                    LeaveQuota::create([
                                        'user_id' => $user->id,
                                        'typeofleave_id' => $type->id,
                                        'year' => $year,
                                        'total_days' => $type->default_quota_days,
                                        'used_days' => 0,
                                    ]);
                                    $count++;
                                }
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil menghasilkan ' . $count . ' data kuota cuti untuk tahun ' . $year)
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveQuotas::route('/'),
        ];
    }

    public static function getLabel(): ?string
    {
        return app()->getLocale() === 'id' ? "Kuota Cuti" : "Leave Quotas";
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Attendance Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-o-ticket';
    }
}
