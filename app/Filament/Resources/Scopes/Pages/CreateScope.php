<?php

namespace App\Filament\Resources\Scopes\Pages;

use App\Filament\Resources\Scopes\ScopeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScope extends CreateRecord
{
    protected static string $resource = ScopeResource::class;
}
