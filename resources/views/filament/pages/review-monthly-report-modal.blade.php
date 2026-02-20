@php
    $summary = $record->getScopeSummary();
@endphp

<div class="space-y-8" style="font-family: ui-sans-serif, system-ui, sans-serif;">
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

    <style>
        .modal-scope { font-family: inherit; }
        .modal-scope .stat-card {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;
            background: #eff6ff; border: 1px solid #bfdbfe;
        }
        .modal-scope .stat-number { font-size: 32px; font-weight: 800; color: #2563eb; line-height: 1; }
        .modal-scope .stat-label { font-size: 14px; font-weight: 600; color: #2563eb; }
        .modal-scope .stat-sub { font-size: 11px; color: #60a5fa; }

        .modal-scope .section-title {
            font-size: 15px; font-weight: 700; margin-bottom: 12px;
            padding-bottom: 8px; display: flex; align-items: center; gap: 6px;
        }
        .modal-scope .section-title.blue { color: #2563eb; border-bottom: 2px solid #93c5fd; }
        .modal-scope .section-title.green { color: #16a34a; border-bottom: 2px solid #86efac; }

        .modal-scope table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
        .modal-scope th {
            text-align: left; font-size: 11px; font-weight: 600; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.05em; padding: 0 12px 4px 12px;
        }
        .modal-scope th:last-child { text-align: right; }

        .modal-scope .row-num {
            padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb;
            border-right: none; border-radius: 8px 0 0 8px;
            font-size: 13px; font-weight: 700; color: #6b7280;
            width: 40px; text-align: center; vertical-align: top;
        }
        .modal-scope .row-name {
            padding: 10px 12px; background: #f9fafb;
            border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
            font-size: 13px; font-weight: 500; color: #1f2937; line-height: 1.5; vertical-align: top;
        }
        .modal-scope .row-badge {
            padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb;
            border-left: none; border-radius: 0 8px 8px 0;
            text-align: right; vertical-align: top; white-space: nowrap;
        }
        .modal-scope .badge-blue {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700;
        }
        .modal-scope .badge-green {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            background: #dcfce7; color: #15803d; font-size: 12px; font-weight: 700;
        }
        .modal-scope .section-gap { margin-bottom: 24px; }

        .dark .modal-scope .stat-card { background: rgba(37,99,235,0.12); border-color: rgba(37,99,235,0.3); }
        .dark .modal-scope .stat-number { color: #60a5fa; }
        .dark .modal-scope .stat-label { color: #93c5fd; }
        .dark .modal-scope .stat-sub { color: #60a5fa; }

        .dark .modal-scope .section-title.blue { color: #60a5fa; border-bottom-color: rgba(59,130,246,0.3); }
        .dark .modal-scope .section-title.green { color: #4ade80; border-bottom-color: rgba(34,197,94,0.3); }

        .dark .modal-scope th { color: #9ca3af; }

        .dark .modal-scope .row-num { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); color: #9ca3af; }
        .dark .modal-scope .row-name { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); color: #e5e7eb; }
        .dark .modal-scope .row-badge { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }

        .dark .modal-scope .badge-blue { background: rgba(59,130,246,0.15); color: #93c5fd; }
        .dark .modal-scope .badge-green { background: rgba(34,197,94,0.15); color: #86efac; }
    </style>

    <div class="modal-scope">
        {{-- Total Days --}}
        <div class="stat-card">
            <div class="stat-number">{{ $summary['total_days'] }}</div>
            <div>
                <div class="stat-label">Hari Laporan</div>
                <div class="stat-sub">Total laporan harian dalam periode ini</div>
            </div>
        </div>

        {{-- Scopes Section --}}
        <div class="section-gap">
            <div class="section-title blue">📋 Ruang Lingkup (Scope)</div>

            @if(empty($summary['scopes']))
                <p style="padding: 12px 16px; font-size: 13px; color: #9ca3af; font-style: italic;">Tidak ada data.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Aktivitas</th>
                            <th>Frekuensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['scopes'] as $name => $count)
                            <tr>
                                <td class="row-num">{{ $loop->iteration }}</td>
                                <td class="row-name">{{ $name }}</td>
                                <td class="row-badge"><span class="badge-blue">{{ $count }}/{{ $summary['total_days'] }} hari</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Other Works Section --}}
        @if(!empty($summary['others']))
        <div>
            <div class="section-title green">💼 Pekerjaan Lain</div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Aktivitas</th>
                        <th>Frekuensi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['others'] as $name => $count)
                        <tr>
                            <td class="row-num">{{ $loop->iteration }}</td>
                            <td class="row-name">{{ $name }}</td>
                            <td class="row-badge"><span class="badge-green">{{ $count }}x</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
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
