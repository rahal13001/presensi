<x-filament-panels::page>
    @php
        $record = $this->getRecord();
    @endphp

    {{-- Report Summary --}}
    <x-filament::section heading="Detail Laporan">
        @include('filament.pages.review-monthly-report-modal', ['record' => $record])
    </x-filament::section>

    {{-- Review Form --}}
    <x-filament::section heading="Form Review Ketua Tim">
        <form wire:submit="approve">
            {{ $this->form }}

            <div class="flex items-center gap-3 mt-6">
                <x-filament::button type="submit" color="success">
                    Setujui Laporan
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Pages\ReviewMonthlyReport::getUrl() }}"
                    color="gray"
                >
                    Batal
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
