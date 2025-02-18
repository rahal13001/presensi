<?php

namespace App\Filament\Resources\MonthlyreportResource\Widgets;

use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use Illuminate\Contracts\View\View as ViewContract;

class TeamLeaderSignature extends Widget
{
    protected static string $view = 'filament.resources.monthlyreport-resource.widgets.team-leader-signature';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->description('Tanda Tangan Ketua Tim Kerja')
                            ->schema([
                                TextInput::make('team_leader')
                                    ->label('Nama Ketua Tim Kerja')
                                    ->maxLength(255)
                                    ->disabled(function ($record) {
                                        $user = auth()->user();
                                        return !$user->hasRole(['super_admin', 'kepala']);
                                    }),

                                TextInput::make('team_idnumber')
                                    ->label('NIP Ketua Tim Kerja')
                                    ->maxLength(255)
                                    ->disabled(function ($record) {
                                        $user = auth()->user();
                                        return !$user->hasRole(['super_admin', 'kepala']);
                                    }),

                                SignaturePad::make('team_sign')
                                    ->label('Tanda Tangan Katimja')
                                    ->dotSize(2.0)
                                    ->lineMinWidth(0.5)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->hidden(function ($record) {
                                        $user = auth()->user();
                                        return !$user->hasRole(['super_admin', 'kepala']);
                                    }),
                            ]),
                    ]),
            ]);
    }

    public function render(): ViewContract
    {
        return view(static::$view);
    }
}
