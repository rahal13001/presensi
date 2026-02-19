<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BasePage;

class Login extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => '',
            'password' => '',
            'remember' => true,
        ]);
    }
}
