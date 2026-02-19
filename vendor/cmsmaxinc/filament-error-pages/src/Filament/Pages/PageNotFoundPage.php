<?php

namespace Cmsmaxinc\FilamentErrorPages\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class PageNotFoundPage extends Page
{
    protected static ?string $slug = '404';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-error-pages::error-page';

    public function getCode(): string
    {
        return '404';
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-error-pages::error-pages.404.title');
    }

    public function getDescription(): string | Htmlable
    {
        return __('filament-error-pages::error-pages.404.description');
    }
}
