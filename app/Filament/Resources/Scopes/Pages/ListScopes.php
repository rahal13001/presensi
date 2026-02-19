<?php

namespace App\Filament\Resources\Scopes\Pages;

use App\Filament\Resources\Scopes\ScopeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScopes extends ListRecords
{
    protected static string $resource = ScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
