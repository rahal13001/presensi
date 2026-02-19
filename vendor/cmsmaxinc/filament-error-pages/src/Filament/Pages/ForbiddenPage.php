<?php

namespace Cmsmaxinc\FilamentErrorPages\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ForbiddenPage extends Page
{
    protected static ?string $slug = '403';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-error-pages::error-page';

    public function getCode(): string
    {
        return '403';
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-error-pages::error-pages.403.title');
    }

    public function getDescription(): string | Htmlable
    {
        return __('filament-error-pages::error-pages.403.description');
    }
}
