@extends('layouts.app')

@section('content')
<div style="margin-bottom:24px">
    <a href="{{ route('cook.dashboard') }}" style="color:var(--terracotta);text-decoration:none;font-size:13px">← Retour aux commandes</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
    {{-- Articles --}}
    <div class="pc-card" style="padding:24px">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px">
            <div>
                <h1 class="pc-title" style="margin:0 0 4px 0">Commande #{{ $order->id }}</h1>
                <p class="pc-subtitle" style="margin:0">Client: {{ $order->client->name }}</p>
            </div>
            <span class="pc-status" style="padding:6px 12px;font-size:12px;background:
                @if($order->status === 'livree') #EFF5F0;color:var(--sage)
                @elseif($order->status === 'annulee') #FFE4E1;color:#c0392b
                @elseif($order->status === 'prete') #EAF0FE;color:#3B6FD4
                @else #FEF0EA;color:var(--terracotta)
                @endif
            ;border-radius:6px">
                {{ str_replace('_', ' ', ucfirst(str_replace('_', ' ', $order->status))) }}
            </span>
        </div>

        {{-- Articles --}}
        <h2 style="font-size:16px;font-weight:600;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)">Articles à préparer</h2>
        <div style="display:flex;flex-direction:column;gap:12px">
            @forelse($order->dishes as $dish)
                @php
                    $lineTotal = $dish->pivot->line_total ?? ($dish->pivot->quantity * $dish->pivot->unit_price);
                @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--warm-white);border-radius:8px;border-left:4px solid var(--terracotta)">
                    <div style="flex:1">
                        <div style="font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:8px">
                            <span style="font-size:20px">{{ $dish->emoji ?? '🍽️' }}</span>
                            {{ $dish->name }}
                        </div>
                        <div style="font-size:13px;color:var(--mid-gray)">
                            Quantité: <strong>{{ $dish->pivot->quantity }}</strong>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:700;color:var(--terracotta);font-size:15px">
                            {{ number_format($lineTotal, 0, ',', ' ') }} FCFA
                        </div>
                        <div style="font-size:12px;color:var(--mid-gray)">
                            {{ $dish->pivot->quantity }} × {{ number_format($dish->pivot->unit_price, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;color:var(--mid-gray);padding:20px">
                    Aucun article
                </div>
            @endforelse
        </div>

        {{-- Livraison --}}
        <div style="margin-top:24px;padding:16px;background:#FEF0EA;border-radius:8px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px">
                <div>
                    <div style="color:var(--mid-gray);font-size:11px;text-transform:uppercase;font-weight:600;margin-bottom:6px">Mode de récupération</div>
                    <div style="font-weight:600">
                        @if($order->fulfillment_type === 'pickup')
                            🏪 Sur place
                        @else
                            🚚 Livraison
                        @endif
                    </div>
                </div>
                <div>
                    <div style="color:var(--mid-gray);font-size:11px;text-transform:uppercase;font-weight:600;margin-bottom:6px">À {{ $order->fulfillment_type === 'pickup' ? 'récupérer' : 'livrer' }}</div>
                    <div style="font-weight:600">{{ $order->pickup_time ? $order->pickup_time->isoFormat('D MMM · HH:mm') : 'Non précisé' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div>
        {{-- Récapitulatif --}}
        <div class="pc-card" style="padding:20px;margin-bottom:14px">
            <h3 style="font-size:13px;font-weight:600;text-transform:uppercase;color:var(--mid-gray);margin:0 0 12px 0">Récapitulatif</h3>
            
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--border)">
                <span style="color:var(--mid-gray)">Total commande</span>
                <span style="font-weight:700;font-family:'Fraunces',serif;font-size:18px">
                    {{ number_format($order->total_price, 0, ',', ' ') }} F
                </span>
            </div>

            <div style="font-size:12px;color:var(--mid-gray);margin-bottom:14px">
                @if($order->is_paid)
                    ✅ <strong style="color:var(--sage)">Client a payé</strong>
                @else
                    ⏳ <strong style="color:var(--terracotta)">En attente de paiement</strong>
                @endif
            </div>

            <div style="font-size:12px;text-transform:uppercase;color:var(--mid-gray);font-weight:600;margin-bottom:6px">Client</div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                <div style="font-weight:600">{{ $order->client->name }}</div>
                @if($order->client->phone)
                    <a href="tel:{{ $order->client->phone }}" style="color:var(--terracotta);text-decoration:none;font-size:12px">
                        📞 {{ $order->client->phone }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Actions de statut --}}
        @if($order->status === 'recue')
            <button onclick="advanceOrder(this, '{{ route('cook.orders.advance', $order) }}')"
                class="pc-btn pc-btn-primary" style="width:100%;padding:12px;justify-content:center">
                👨‍🍳 Commencer la préparation
            </button>
        @elseif($order->status === 'en_preparation')
            <button onclick="advanceOrder(this, '{{ route('cook.orders.advance', $order) }}')"
                class="pc-btn pc-btn-primary" style="width:100%;padding:12px;justify-content:center;background:#2ecc71;border-color:#2ecc71">
                ✅ Marquer comme prête
            </button>
        @elseif($order->status === 'prete')
            <button onclick="advanceOrder(this, '{{ route('cook.orders.advance', $order) }}')"
                class="pc-btn pc-btn-primary" style="width:100%;padding:12px;justify-content:center">
                🚚 Commande livrée
            </button>
        @else
            <div style="text-align:center;padding:20px;color:var(--sage);background:#EFF5F0;border-radius:8px;font-weight:600">
                ✅ Commande livrée
            </div>
        @endif

        <a href="{{ route('cook.dashboard') }}" class="pc-btn" style="width:100%;padding:10px;justify-content:center;margin-top:8px">
            Retour
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
function advanceOrder(btn, url) {
    btn.disabled = true;
    btn.textContent = '…';

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrf,
        },
        body: '_method=PATCH',
    })
    .then(res => {
        if (res.ok || res.redirected) {
            window.location.reload();
        } else {
            btn.disabled = false;
            btn.textContent = 'Erreur';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Erreur';
    });
}
</script>
@endpush
