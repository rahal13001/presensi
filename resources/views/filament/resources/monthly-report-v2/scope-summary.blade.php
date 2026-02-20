@php
    $summary = $getRecord()->getScopeSummary();
    $totalDays = $summary['total_days'] ?? 0;
    $scopes = $summary['scopes'] ?? [];
    $others = $summary['others'] ?? [];

    // Detect dark mode via Filament's data attribute
    // We use CSS media query approach for colors
@endphp

<style>
    .scope-summary { font-family: inherit; }
    .scope-summary .stat-card {
        display: flex; align-items: center; gap: 12px;
        padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;
        background: #eff6ff; border: 1px solid #bfdbfe;
    }
    .scope-summary .stat-number { font-size: 32px; font-weight: 800; color: #2563eb; line-height: 1; }
    .scope-summary .stat-label { font-size: 14px; font-weight: 600; color: #2563eb; }
    .scope-summary .stat-sub { font-size: 11px; color: #60a5fa; }

    .scope-summary .section-title {
        font-size: 15px; font-weight: 700; margin-bottom: 12px;
        padding-bottom: 8px; display: flex; align-items: center; gap: 6px;
    }
    .scope-summary .section-title.blue { color: #2563eb; border-bottom: 2px solid #93c5fd; }
    .scope-summary .section-title.green { color: #16a34a; border-bottom: 2px solid #86efac; }

    .scope-summary table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
    .scope-summary th {
        text-align: left; font-size: 11px; font-weight: 600; color: #6b7280;
        text-transform: uppercase; letter-spacing: 0.05em; padding: 0 12px 4px 12px;
    }
    .scope-summary th:last-child { text-align: right; }

    .scope-summary .row-num {
        padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb;
        border-right: none; border-radius: 8px 0 0 8px;
        font-size: 13px; font-weight: 700; color: #6b7280;
        width: 40px; text-align: center; vertical-align: top;
    }
    .scope-summary .row-name {
        padding: 10px 12px; background: #f9fafb;
        border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
        font-size: 13px; font-weight: 500; color: #1f2937; line-height: 1.5; vertical-align: top;
    }
    .scope-summary .row-badge {
        padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb;
        border-left: none; border-radius: 0 8px 8px 0;
        text-align: right; vertical-align: top; white-space: nowrap;
    }
    .scope-summary .badge-blue {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700;
    }
    .scope-summary .badge-green {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        background: #dcfce7; color: #15803d; font-size: 12px; font-weight: 700;
    }
    .scope-summary .section-gap { margin-bottom: 24px; }

    /* Dark mode overrides */
    .dark .scope-summary .stat-card { background: rgba(37,99,235,0.12); border-color: rgba(37,99,235,0.3); }
    .dark .scope-summary .stat-number { color: #60a5fa; }
    .dark .scope-summary .stat-label { color: #93c5fd; }
    .dark .scope-summary .stat-sub { color: #60a5fa; }

    .dark .scope-summary .section-title.blue { color: #60a5fa; border-bottom-color: rgba(59,130,246,0.3); }
    .dark .scope-summary .section-title.green { color: #4ade80; border-bottom-color: rgba(34,197,94,0.3); }

    .dark .scope-summary th { color: #9ca3af; }

    .dark .scope-summary .row-num { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); color: #9ca3af; }
    .dark .scope-summary .row-name { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); color: #e5e7eb; }
    .dark .scope-summary .row-badge { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }

    .dark .scope-summary .badge-blue { background: rgba(59,130,246,0.15); color: #93c5fd; }
    .dark .scope-summary .badge-green { background: rgba(34,197,94,0.15); color: #86efac; }

    /* Mobile: make table cells stack better */
    @media (max-width: 480px) {
        .scope-summary .row-name { font-size: 12px; }
        .scope-summary .badge-blue, .scope-summary .badge-green { font-size: 11px; padding: 2px 8px; }
    }
</style>

<div class="scope-summary">

    {{-- Total Days --}}
    <div class="stat-card">
        <div class="stat-number">{{ $totalDays }}</div>
        <div>
            <div class="stat-label">Hari Laporan</div>
            <div class="stat-sub">Total laporan harian dalam periode ini</div>
        </div>
    </div>

    {{-- Scopes Section --}}
    <div class="section-gap">
        <div class="section-title blue">📋 Ruang Lingkup (Scope)</div>

        @if(empty($scopes))
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
                    @foreach($scopes as $name => $count)
                        <tr>
                            <td class="row-num">{{ $loop->iteration }}</td>
                            <td class="row-name">{{ $name }}</td>
                            <td class="row-badge"><span class="badge-blue">{{ $count }}/{{ $totalDays }} hari</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Other Works Section --}}
    @if(!empty($others))
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
                @foreach($others as $name => $count)
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
