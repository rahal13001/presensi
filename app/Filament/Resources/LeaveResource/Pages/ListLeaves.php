<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\LeaveResource\Widgets\LeaveQuotaOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('Semua')
                ->badge(static::getResource()::getEloquentQuery()->count()),
            'pending' => \Filament\Schemas\Components\Tabs\Tab::make('Menunggu')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending'))
                ->badge(static::getResource()::getEloquentQuery()->where('status', 'pending')->count()),
            'approved' => \Filament\Schemas\Components\Tabs\Tab::make('Disetujui')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'approved'))
                ->badge(static::getResource()::getEloquentQuery()->where('status', 'approved')->count()),
            'rejected' => \Filament\Schemas\Components\Tabs\Tab::make('Ditolak')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'rejected'))
                ->badge(static::getResource()::getEloquentQuery()->where('status', 'rejected')->count()),
        ];
    }
}
