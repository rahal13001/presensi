<?php

namespace App\Filament\Resources\Holidays\Pages;

use App\Filament\Resources\Holidays\HolidayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('syncApi')
                ->label('Tarik Data API')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Proses ini akan mensinkronisasi data hari libur nasional dari server (libur.deno.dev). Lanjutkan?')
                ->action(function () {
                    try {
                        $response = \Illuminate\Support\Facades\Http::get('https://libur.deno.dev/api');
                        
                        if ($response->successful()) {
                            $holidays = $response->json();
                            $count = 0;
                            foreach ($holidays as $holiday) {
                                // Provide default values to handle invalid API items
                                if (isset($holiday['date']) && isset($holiday['name'])) {
                                    \App\Models\Holiday::updateOrCreate(
                                        ['date' => $holiday['date']],
                                        ['name' => $holiday['name']]
                                    );
                                    $count++;
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil sinkronisasi ' . $count . ' hari libur.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal mengambil data dari API.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                         \Filament\Notifications\Notification::make()
                                ->title('Terjadi kesalahan koneksi saat menghubungi server API.')
                                ->danger()
                                ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
