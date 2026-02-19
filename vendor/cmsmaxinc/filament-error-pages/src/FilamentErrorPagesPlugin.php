<?php

namespace Cmsmaxinc\FilamentErrorPages;

use Cmsmaxinc\FilamentErrorPages\Filament\Pages\ForbiddenPage;
use Cmsmaxinc\FilamentErrorPages\Filament\Pages\PageNotFoundPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

use function filament;

class FilamentErrorPagesPlugin implements Plugin
{
    protected array $routes = [];

    protected bool $onlyShowForConfiguredRoutes = false;

    public function getId(): string
    {
        return 'filament-error-pages';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            PageNotFoundPage::class,
            ForbiddenPage::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function routes(array $routes): static
    {
        $this->routes = $routes;

        return $this;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function onlyShowForConfiguredRoutes(): static
    {
        $this->onlyShowForConfiguredRoutes = true;

        return $this;
    }

    public function shouldOnlyShowForConfiguredRoutes(): bool
    {
        return $this->onlyShowForConfiguredRoutes;
    }
}
