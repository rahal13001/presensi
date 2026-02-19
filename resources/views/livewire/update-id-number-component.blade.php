<x-filament-breezy::grid-section md=2 title="Update NIP / NIK" description="Kelola Informasi NIP / NIK Anda">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit">
                   Perbarui
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
