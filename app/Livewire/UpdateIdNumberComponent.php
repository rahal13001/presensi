<?php

namespace App\Livewire;

use Livewire\Component;
// use Filament\Forms\Form;
use Filament\Forms\Form;
// use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Crypt;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class UpdateIdNumberComponent extends MyProfileComponent
{
    protected string $view = 'livewire.update-id-number-component'; // Your Blade view file
    public array $only = ['idnumber'];  // Only updating id_number
    public array $data;
    public $user;
    public $userClass;

    public function mount()
    {
        $this->user = auth()->user();
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('idnumber')
                    ->label('NIP / NIK')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->validateOnly('data.idnumber');

        $data = collect($this->form->getState())->only($this->only)->all();
        $this->user->update($data);
        Notification::make()
            ->success()
            ->title(__('NIK / NIP berhasil diperbarui'))
            ->send();

        
    }
}
