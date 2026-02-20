<?php

namespace App\Filament\Resources\OtherWorkOptions\Pages;

use App\Filament\Resources\OtherWorkOptions\OtherWorkOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOtherWorkOption extends EditRecord
{
    protected static string $resource = OtherWorkOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin'])),
        ];
    }
}
