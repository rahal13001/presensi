@php
    $summary = $record->getScopeSummary();
@endphp

<div class="space-y-6" style="font-family: ui-sans-serif, system-ui, sans-serif;">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div>
            <div class="text-sm font-semibold text-gray-500" style="font-size: 0.875rem; font-weight: 600; color: #6b7280;">Pegawai</div>
            <div class="text-lg" style="font-size: 1.125rem; line-height: 1.75rem;">{{ $record->user->name }}</div>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-500" style="font-size: 0.875rem; font-weight: 600; color: #6b7280;">Periode</div>
            <div class="text-lg" style="font-size: 1.125rem; line-height: 1.75rem;">
                {{ \Carbon\Carbon::create()->month($record->month)->translatedFormat('F') }} {{ $record->year }}
            </div>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-500" style="font-size: 0.875rem; font-weight: 600; color: #6b7280;">Nama Tim</div>
            <div class="text-lg" style="font-size: 1.125rem; line-height: 1.75rem;">{{ $record->team_name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-500" style="font-size: 0.875rem; font-weight: 600; color: #6b7280;">Ketua Tim</div>
            <div class="text-lg" style="font-size: 1.125rem; line-height: 1.75rem;">{{ $record->team_leader_name ?? '-' }}</div>
        </div>
    </div>

    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700" style="border-radius: 0.5rem; padding: 1rem; border-width: 1px;">
        <h3 class="font-bold mb-3 text-lg" style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1.125rem;">Ringkasan Aktivitas</h3>
        <p class="text-sm mb-2" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Total Hari Laporan: <strong>{{ $summary['total_days'] }}</strong></p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <h4 class="font-semibold text-sm mb-2" style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem;">Scope Checklist</h4>
                <ul class="list-disc pl-5 text-sm space-y-1" style="list-style-type: disc; padding-left: 1.25rem; font-size: 0.875rem;">
                    @forelse($summary['scopes'] as $scope => $count)
                        <li style="margin-bottom: 0.25rem;">{{ $scope }}: <strong>{{ $count }}</strong> hari</li>
                    @empty
                        <li class="text-gray-500 italic" style="color: #6b7280; font-style: italic;">Tidak ada scope checklist.</li>
                    @endforelse
                </ul>
            </div>
            
            @if(!empty($summary['others']))
            <div>
                <h4 class="font-semibold text-sm mb-2" style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem;">Pekerjaan Lain</h4>
                <ul class="list-disc pl-5 text-sm space-y-1" style="list-style-type: disc; padding-left: 1.25rem; font-size: 0.875rem;">
                    @foreach($summary['others'] as $work => $count)
                        <li style="margin-bottom: 0.25rem;">{{ $work }}: <strong>{{ $count }}</strong> kali</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div>
        <h3 class="font-bold mb-3 text-lg" style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1.125rem;">Foto Terpilih</h3>
        <div class="max-h-96 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 24rem; overflow-y: auto;">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                @foreach($record->photos as $photo)
                    @if($photo->dailyPhoto)
                        <div class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm" style="aspect-ratio: 1/1;">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->dailyPhoto->photo_path) }}" 
                                 alt="Foto Dokumentasi" 
                                 class="w-full h-full object-cover cursor-pointer hover:scale-110 transition-transform duration-300"
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onclick="window.open(this.src, '_blank')"
                            >
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors pointer-events-none"></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @if($record->photos->isEmpty())
            <p class="text-sm text-gray-500 italic" style="font-size: 0.875rem; color: #6b7280; font-style: italic;">Tidak ada foto terpilih.</p>
        @endif
    </div>

    <div>
        <h3 class="font-bold mb-3 text-lg" style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1.125rem;">Tanda Tangan Pegawai</h3>
        <div class="flex items-center gap-4 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900" style="display: flex; align-items: center; gap: 1rem; border-width: 1px; border-radius: 0.5rem; padding: 1rem;">
            @if($record->employee_sign)
                <img src="{{ str_starts_with($record->employee_sign, 'data:image') ? $record->employee_sign : \Illuminate\Support\Facades\Storage::url($record->employee_sign) }}" alt="Tanda Tangan Pegawai" class="h-24 object-contain" style="height: 6rem; object-fit: contain;">
                <div class="text-sm text-gray-500" style="font-size: 0.875rem; color: #9ca3af;">
                    {{ $record->city ?? 'Sorong' }}, {{ $record->signed_date ? $record->signed_date->isoFormat('D MMMM Y') : '-' }}<br>
                    Ditandatangani pada:<br>
                    <strong>{{ $record->employee_signed_at ? $record->employee_signed_at->format('d/m/Y H:i') : '-' }}</strong>
                </div>
            @else
                <p class="text-sm text-red-500 italic" style="font-size: 0.875rem; color: #ef4444; font-style: italic;">Belum ditandatangani pegawai.</p>
            @endif
        </div>
    </div>
</div>
