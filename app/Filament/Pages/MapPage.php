<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MapPage extends Page
{

    protected string $view = 'filament.pages.map-page';

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-o-document-text';
    }
}
