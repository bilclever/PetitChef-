@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
    <div>
        <h1 class="pc-title">Espace <em style="font-style:italic;color:var(--terracotta)">Cuisinier</em></h1>
        <p class="pc-subtitle">{{ now()->isoFormat('dddd D MMMM YYYY') }} · Service du jour</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">

        {{-- Panneau boutique cohérent --}}
        @php
            $cook = auth()->user();
            $isOpen = $cook->isShopOpen();
            $closesAt = $cook->shop_closes_at;
            // Statut textuel cohérent
            if ($isOpen && $closesAt) {
                $shopStatusText = 'Ouvert · clôture à ' . $closesAt;
            } elseif ($isOpen) {
                $shopStatusText = 'Ouvert';
            } else {
                $shopStatusText = $closesAt ? 'Fermé (clôture à ' . $closesAt . ')' : 'Fermé manuellement';
            }
        @endphp

        {{-- Badge statut --}}
        <div id="shop-status-badge" style="display:flex;align-items:center;gap:8px;background:var(--warm-white);border:1px solid var(--border);border-radius:10px;padding:6px 12px">
            <span id="shop-dot" style="width:8px;height:8px;border-radius:50%;background:{{ $isOpen ? '#2ecc71' : '#c0392b' }};display:inline-block;transition:background .3s"></span>
            <span id="shop-label" style="font-size:12px;font-weight:600;color:{{ $isOpen ? 'var(--sage)' : '#c0392b' }}">
                {{ $shopStatusText }}
            </span>
        </div>

        {{-- Bouton toggle --}}
        <button id="shop-toggle-btn"
            onclick="toggleShop(this)"
            data-url="{{ route('cook.shop.toggle') }}"
            data-is-open="{{ $isOpen ? '1' : '0' }}"
            class="pc-btn {{ $isOpen ? '' : 'pc-btn-primary' }}"
            style="padding:7px 14px">
            {{ $isOpen ? '🔴 Fermer maintenant' : '🟢 Ouvrir maintenant' }}
        </button>

        {{-- Heure de clôture automatique --}}
        <form id="closing-time-form" method="POST" action="{{ route('cook.shop.closing-time') }}"
            style="display:flex;gap:6px;align-items:center"
            onsubmit="submitClosingTime(event, this)">
            @csrf @method('PATCH')
            <input type="time" name="shop_closes_at" id="shop-closes-at" class="pc-input"
                value="{{ $closesAt ?? '' }}"
                style="width:110px;padding:7px 10px"
                title="Clôture automatique à cette heure">
            <button type="submit" class="pc-btn" style="padding:7px 10px;white-space:nowrap">⏰ Appliquer</button>
            @if($closesAt)
            <button type="button" onclick="clearClosingTime()" class="pc-btn" style="padding:7px 10px;color:#c0392b;border-color:#e6b2ac" title="Supprimer l'heure de clôture">✕</button>
            @endif
        </form>

        <a href="{{ route('menu') }}" class="pc-btn" style="padding:7px 14px">Voir le menu</a>
        <a href="{{ route('cook.dishes.create') }}" class="pc-btn pc-btn-primary" style="padding:7px 14px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter un plat
        </a>
    </div>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="pc-card" style="padding:18px">
        <div style="width:36px;height:36px;border-radius:9px;background:#FEF0EA;color:var(--terracotta);display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div style="font-family:'Fraunces',serif;font-size:28px;font-weight:700;line-height:1">{{ $stats['commandes'] }}</div>
        <div style="font-size:12px;color:var(--mid-gray);margin-top:4px">Commandes reçues</div>
    </div>
    <div class="pc-card" style="padding:18px">
        <div style="width:36px;height:36px;border-radius:9px;background:#EFF5F0;color:var(--sage);display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div style="font-family:'Fraunces',serif;font-size:28px;font-weight:700;line-height:1">{{ $stats['livrees'] }}</div>
        <div style="font-size:12px;color:var(--mid-gray);margin-top:4px">Livrées</div>
    </div>
    <div class="pc-card" style="padding:18px">
        <div style="width:36px;height:36px;border-radius:9px;background:#EAF0FE;color:#3B6FD4;display:flex;align-items:center;justify-content:center;margin-bottom:10px;font-weight:700;font-size:13px">
            FCFA
        </div>
        <div style="font-family:'Fraunces',serif;font-size:28px;font-weight:700;line-height:1">{{ number_format($stats['fcfa'], 0, ',', ' ') }}</div>
        <div style="font-size:12px;color:var(--mid-gray);margin-top:4px">FCFA encaissés</div>
    </div>
    <div class="pc-card" style="padding:18px">
        <div style="width:36px;height:36px;border-radius:9px;background:var(--light-gray);color:var(--mid-gray);display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
        </div>
        <div style="font-family:'Fraunces',serif;font-size:28px;font-weight:700;line-height:1">{{ $stats['plats'] }}</div>
        <div style="font-size:12px;color:var(--mid-gray);margin-top:4px">Plats publiés</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:18px">
    {{-- Commandes en cours --}}
    <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--mid-gray);margin-bottom:12px">Commandes en cours</div>
        <div class="pc-table-wrap">
            <table class="pc-table">
                <thead>
                    <tr><th>N°</th><th>Client</th><th>Récup.</th><th>Total</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody data-cook-orders-body>
                    @forelse($orders as $order)
                    <tr data-order-row="{{ $order->id }}">
                        <td><a href="{{ route('cook.orders.show', $order) }}" style="color:var(--terracotta);text-decoration:none;font-weight:600">#{{ $order->id }}</a></td>
                        <td>{{ $order->client->name }}</td>
                        <td style="color:var(--mid-gray)">{{ $order->pickup_time ? $order->pickup_time->format('d/m H:i') : '—' }}</td>
                        <td>{{ number_format($order->total_price, 0, ',', ' ') }} F</td>
                        <td>
                            <span class="pc-status pc-status-pending" data-order-status="{{ $order->id }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
                        </td>
                        <td data-order-action="{{ $order->id }}" data-advance-url="{{ route('cook.orders.advance', $order) }}">
                            @if(in_array($order->status, ['recue', 'en_preparation', 'prete']))
                                @php
                                    $btnLabel = match($order->status) {
                                        'recue' => 'Préparer',
                                        'en_preparation' => 'Prête',
                                        'prete' => 'Livrée',
                                    };
                                    $btnStyle = $order->status === 'recue'
                                        ? 'pc-btn pc-btn-primary'
                                        : ($order->status === 'en_preparation'
                                            ? 'pc-btn'
                                            : 'pc-btn');
                                    $extraStyle = $order->status === 'en_preparation'
                                        ? 'border-color:var(--sage);color:var(--sage)'
                                        : '';
                                @endphp
                                <button
                                    class="{{ $btnStyle }}"
                                    style="padding:5px 10px;font-size:12px;{{ $extraStyle }}"
                                    onclick="advanceOrder(this, '{{ route('cook.orders.advance', $order) }}')"
                                >{{ $btnLabel }}</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr data-cook-orders-empty><td colspan="6" style="text-align:center;color:var(--mid-gray);padding:24px">Aucune commande en cours</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mes plats --}}
    <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--mid-gray);margin-bottom:12px">Mes plats du jour</div>
        <div style="display:flex;flex-direction:column;gap:10px">
            @forelse($dishes as $dish)
            <div class="pc-card" style="padding:0;{{ $dish->is_of_day ? 'border-color:var(--terracotta)' : '' }}">
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px">
                    @if($dish->photo_path)
                        <img src="{{ asset('storage/'.$dish->photo_path) }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;flex-shrink:0">
                    @else
                        <div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,#F5DEB3,#DEB887);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">{{ $dish->emoji ?? '🍽️' }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;display:flex;align-items:center;gap:6px">
                            {{ $dish->name }}
                            @if($dish->is_of_day)
                                <span style="font-size:10px;background:#FEF0EA;color:var(--terracotta);padding:1px 7px;border-radius:10px;font-weight:600">⭐ Plat du jour</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--mid-gray)">
                            {{ number_format($dish->price, 0, ',', ' ') }} FCFA ·
                            <strong style="{{ $dish->quantity <= 3 ? 'color:var(--terracotta)' : 'color:var(--sage)' }}">{{ $dish->quantity }} restants</strong>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0">
                        <form method="POST" action="{{ route('cook.dishes.toggle-ofday', $dish) }}">
                            @csrf @method('PATCH')
                            <button class="pc-btn" style="padding:5px 8px;font-size:13px" title="Plat du jour">⭐</button>
                        </form>
                        <a href="{{ route('cook.dishes.edit', $dish) }}" class="pc-btn" style="padding:5px 8px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('cook.dishes.destroy', $dish) }}" onsubmit="return confirm('Supprimer ce plat ?')">
                            @csrf @method('DELETE')
                            <button class="pc-btn" style="padding:5px 8px;border-color:#e6b2ac;color:#c0392b">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:32px;color:var(--mid-gray);font-size:13px;border:2px dashed var(--border);border-radius:12px">
                Aucun plat publié —
                <a href="{{ route('cook.dishes.create') }}" style="color:var(--terracotta);font-weight:600">ajouter un plat</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media (max-width: 900px) {
    div[style*="grid-template-columns:repeat(4,1fr)"] { grid-template-columns: repeat(2,1fr) !important; }
    div[style*="grid-template-columns:1.2fr 1fr"] { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@push('scripts')
<script>
// ─── Toggle boutique ──────────────────────────────────────────────────────────
function toggleShop(btn) {
    btn.disabled = true;
    const url = btn.dataset.url;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: '_method=PATCH',
    })
    .then(r => r.json())
    .then(data => {
        const isOpen = data.is_open;
        const closesAt = document.getElementById('shop-closes-at')?.value || null;

        // Badge
        const dot = document.getElementById('shop-dot');
        const label = document.getElementById('shop-label');
        if (dot) dot.style.background = isOpen ? '#2ecc71' : '#c0392b';
        if (label) {
            label.style.color = isOpen ? 'var(--sage)' : '#c0392b';
            if (isOpen && closesAt)      label.textContent = 'Ouvert · clôture à ' + closesAt;
            else if (isOpen)             label.textContent = 'Ouvert';
            else if (closesAt)           label.textContent = 'Fermé (clôture à ' + closesAt + ')';
            else                         label.textContent = 'Fermé manuellement';
        }

        // Bouton
        btn.dataset.isOpen = isOpen ? '1' : '0';
        btn.className = 'pc-btn' + (isOpen ? '' : ' pc-btn-primary');
        btn.textContent = isOpen ? '🔴 Fermer maintenant' : '🟢 Ouvrir maintenant';
        btn.disabled = false;

        if (window.pcShowToast) window.pcShowToast(
            isOpen ? '🟢 Boutique ouverte' : '🔴 Boutique fermée',
            data.message, isOpen ? 'success' : 'warning'
        );
    })
    .catch(() => { btn.disabled = false; });
}

// ─── Heure de clôture ─────────────────────────────────────────────────────────
function submitClosingTime(e, form) {
    e.preventDefault();
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const time = document.getElementById('shop-closes-at').value;

    fetch(form.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: '_method=PATCH&shop_closes_at=' + encodeURIComponent(time),
    })
    .then(r => r.json())
    .then(data => {
        const label = document.getElementById('shop-label');
        const isOpen = document.getElementById('shop-toggle-btn')?.dataset.isOpen === '1';
        if (label) {
            if (isOpen && time)      label.textContent = 'Ouvert · clôture à ' + time;
            else if (isOpen)         label.textContent = 'Ouvert';
            else if (time)           label.textContent = 'Fermé (clôture à ' + time + ')';
            else                     label.textContent = 'Fermé manuellement';
        }
        if (window.pcShowToast) window.pcShowToast('⏰ Clôture mise à jour', data.message, 'info');
    });
}

function clearClosingTime() {
    document.getElementById('shop-closes-at').value = '';
    const form = document.getElementById('closing-time-form');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(form.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: '_method=PATCH&shop_closes_at=',
    })
    .then(r => r.json())
    .then(data => {
        const label = document.getElementById('shop-label');
        const isOpen = document.getElementById('shop-toggle-btn')?.dataset.isOpen === '1';
        if (label) label.textContent = isOpen ? 'Ouvert' : 'Fermé manuellement';
        if (window.pcShowToast) window.pcShowToast('⏰ Clôture supprimée', data.message, 'info');
    });
}

// ─── Avancer statut commande ──────────────────────────────────────────────────
function advanceOrder(btn, url) {
    btn.disabled = true;
    btn.textContent = '…';
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf },
        body: '_method=PATCH',
    })
    .then(res => { if (res.ok || res.redirected) window.location.reload(); else { btn.disabled = false; btn.textContent = 'Erreur'; } })
    .catch(() => { btn.disabled = false; btn.textContent = 'Erreur'; });
}
</script>
@endpush
