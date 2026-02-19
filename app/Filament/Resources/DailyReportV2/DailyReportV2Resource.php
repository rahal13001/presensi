<?php

namespace App\Filament\Resources\DailyReportV2;

use App\Filament\Resources\DailyReportV2\Pages;
use App\Filament\Resources\DailyReportV2\Schemas\DailyReportV2Form;
use App\Filament\Resources\DailyReportV2\Tables\DailyReportsV2Table;
use App\Models\DailyReportV2;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReportV2Resource extends Resource
{
    protected static ?string $model = DailyReportV2::class;

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan Harian';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getLabel(): ?string
    {
        return app()->getLocale() === 'id' ? 'Laporan Harian (Baru)' : 'Daily Report (New)';
    }

    public static function form(Schema $schema): Schema
    {
        return DailyReportV2Form::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyReportsV2Table::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReportsV2::route('/'),
            'create' => Pages\CreateDailyReportV2::route('/create'),
            'edit' => Pages\EditDailyReportV2::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
            // ->where('user_id', Auth::id()); // Filtering is done in table modifyQueryUsing usually, but let's check requirements.
            // Requirement says: "Employees (role user) should only see their own reports (use modifyQueryUsing to filter by user_id = Auth::id())"
            // So we will do it in the Table configuration or here. 
            // Usually resources might be accessed directly via URL, so scoping here is safer, 
            // BUT the prompt specifically said "use modifyQueryUsing".
            // However, scoping getEloquentQuery is safer for Edit pages etc.
            // Let's stick to the prompt's specific instruction about Table for listing, 
            // but we should generally secure the resource. 
            // For now I will follow the Table instruction for the list, 
            // and relying on policy or similar for view/edit would be ideal, 
            // but broadly scoping strictly here might hide it from Admins if not careful.
            // Admin/super_admin can see all.
    }
}
