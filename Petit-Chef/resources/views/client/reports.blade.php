@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
    <div>
        <h1 class="pc-title">Mes <em style="font-style:italic;color:var(--terracotta)">signalements</em></h1>
        <p class="pc-subtitle">{{ $reports->total() }} signalement(s)</p>
    </div>
    <a href="{{ route('client.reports.create') }}" class="pc-btn pc-btn-primary">
        + Nouveau signalement
    </a>
</div>

@if($reports->isEmpty())
    <div id="client-reports-empty" class="pc-card" style="padding:48px;text-align:center;color:var(--mid-gray)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 14px;display:block;opacity:.4">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        Aucun signalement pour le moment.
        <div style="margin-top:12px">
            <a href="{{ route('client.reports.create') }}" style="color:var(--terracotta);font-weight:600">Faire un signalement</a>
        </div>
    </div>
@else
    <div id="client-reports-list" style="display:flex;flex-direction:column;gap:12px">
        @foreach($reports as $report)
        @php
            $sc = $statusColors[$report->status] ?? ['bg' => '#FEF0EA', 'color' => '#C2623F'];
        @endphp
        <div class="pc-card" data-report-card="{{ $report->id }}" style="padding:18px">
            <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;flex-wrap:wrap">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
                        <strong style="font-size:14px" data-report-type="{{ $report->id }}">{{ $typeLabels[$report->type] ?? $report->type }}</strong>
                        <span data-report-status="{{ $report->id }}" style="padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">
                            {{ $statusLabels[$report->status] ?? $report->status }}
                        </span>
                    </div>
                    <div data-report-meta="{{ $report->id }}" style="font-size:12px;color:var(--mid-gray);margin-bottom:8px">
                        Cuisinier : <strong>{{ $report->cook_name ?? '—' }}</strong>
                        @if($report->order_ref)
                            · Commande #{{ $report->order_ref }}
                        @endif
                        · {{ \Carbon\Carbon::parse($report->created_at)->isoFormat('D MMM YYYY') }}
                    </div>
                    <div data-report-description="{{ $report->id }}" style="font-size:13px;color:var(--charcoal);line-height:1.6">
                        {{ Str::limit($report->description, 200) }}
                    </div>

                    @if($report->admin_note)
                    <div data-report-admin-note="{{ $report->id }}" style="margin-top:10px;padding:10px 14px;background:#EAF0FE;border-radius:8px;font-size:12px;color:#2B50A0;border-left:3px solid #3B6FD4">
                        <strong>Réponse admin :</strong> {{ $report->admin_note }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:16px">{{ $reports->links() }}</div>
@endif
@endsection
