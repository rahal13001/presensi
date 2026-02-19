<?php

namespace App\Filament\Resources\Scopes\Pages;

use App\Filament\Resources\Scopes\ScopeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScope extends EditRecord
{
    protected static string $resource = ScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
