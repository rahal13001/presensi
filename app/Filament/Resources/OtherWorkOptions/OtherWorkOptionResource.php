<?php

namespace App\Filament\Resources\OtherWorkOptions;

use App\Filament\Resources\OtherWorkOptions\Pages\CreateOtherWorkOption;
use App\Filament\Resources\OtherWorkOptions\Pages\EditOtherWorkOption;
use App\Filament\Resources\OtherWorkOptions\Pages\ListOtherWorkOptions;
use App\Filament\Resources\OtherWorkOptions\Schemas\OtherWorkOptionForm;
use App\Filament\Resources\OtherWorkOptions\Tables\OtherWorkOptionsTable;
use App\Models\OtherWorkOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OtherWorkOptionResource extends Resource
{
    protected static ?string $model = OtherWorkOption::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OtherWorkOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OtherWorkOptionsTable::configure($table);
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
            'index' => ListOtherWorkOptions::route('/'),
            'create' => CreateOtherWorkOption::route('/create'),
            'edit' => EditOtherWorkOption::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return 'Pekerjaan Lain';
        }

        return 'Other Work Option';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Master Data';
    }

    public static function getNavigationIcon(): string|null
    {
        return 'heroicon-o-briefcase';
    }
}
