@extends('layouts.app')

@section('content')
<div style="margin-bottom:24px">
    <a href="{{ route('menu') }}" style="color:var(--terracotta);text-decoration:none;font-size:13px">← Retour au menu</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
    {{-- Détails principaux --}}
    <div class="pc-card" style="padding:24px">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px">
            <div>
                <h1 class="pc-title" style="margin:0 0 4px 0">Commande #{{ $order->id }}</h1>
                <p class="pc-subtitle" style="margin:0">{{ $order->created_at->isoFormat('dddd D MMMM YYYY à HH:mm') }}</p>
            </div>
            <span id="order-status-badge" data-order-status="{{ $order->id }}" style="padding:6px 12px;font-size:12px;background:
                @if($order->status === 'livree') #EFF5F0;color:var(--sage)
                @elseif($order->status === 'annulee') #FFE4E1;color:#c0392b
                @elseif($order->status === 'prete') #EAF0FE;color:#3B6FD4
                @else #FEF0EA;color:var(--terracotta)
                @endif
            ;border-radius:6px;display:inline-flex;align-items:center;font-weight:600">
                {{ str_replace('_', ' ', ucfirst(str_replace('_', ' ', $order->status))) }}
            </span>
        </div>

        {{-- Articles --}}
        <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)">
            <h2 style="font-size:16px;font-weight:600;margin-bottom:14px">Articles commandés</h2>
            <div style="display:flex;flex-direction:column;gap:10px">
                @forelse($order->dishes as $dish)
                    @php
                        $lineTotal = $dish->pivot->line_total ?? ($dish->pivot->quantity * $dish->pivot->unit_price);
                    @endphp
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--warm-white);border-radius:8px">
                        <div style="flex:1">
                            <div style="font-weight:600;margin-bottom:3px">{{ $dish->name }}</div>
                            <div style="font-size:12px;color:var(--mid-gray)">
                                {{ $dish->pivot->quantity }} × {{ number_format($dish->pivot->unit_price, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div style="font-weight:700;color:var(--terracotta);text-align:right;min-width:100px">
                            {{ number_format($lineTotal, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--mid-gray);padding:20px">
                        Aucun article
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Infos livraison --}}
        <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)">
            <h2 style="font-size:16px;font-weight:600;margin-bottom:14px">Livraison</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:14px">
                <div>
                    <div style="color:var(--mid-gray);font-size:12px;text-transform:uppercase;font-weight:600;margin-bottom:4px">Mode</div>
                    <div style="font-weight:500">
                        @if($order->fulfillment_type === 'pickup')
                            🏪 Récupération sur place
                        @else
                            🚚 Livraison à domicile
                        @endif
                    </div>
                </div>
                <div>
                    <div style="color:var(--mid-gray);font-size:12px;text-transform:uppercase;font-weight:600;margin-bottom:4px">À {{ $order->fulfillment_type === 'pickup' ? 'récupérer' : 'livrer' }}</div>
                    <div style="font-weight:500">{{ $order->pickup_time ? $order->pickup_time->isoFormat('D MMMM à HH:mm') : 'Non précisé' }}</div>
                </div>
            </div>
        </div>

        {{-- Cuisinier --}}
        <div style="margin-bottom:24px;padding:14px;background:#FEF0EA;border-radius:8px">
            <div style="color:var(--mid-gray);font-size:12px;text-transform:uppercase;font-weight:600;margin-bottom:6px">Préparé par</div>
            <div style="display:flex;align-items:center;gap:10px">
                <div style="font-weight:600">{{ $order->cook->name }}</div>
                @if($order->cook->phone)
                    <div style="font-size:12px;color:var(--mid-gray)">
                        📞 <a href="tel:{{ $order->cook->phone }}" style="color:var(--terracotta);text-decoration:none">{{ $order->cook->phone }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Résumé paiement --}}
    <div>
        {{-- Prix --}}
        <div class="pc-card" style="padding:20px;margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--border)">
                <span style="color:var(--mid-gray)">Total</span>
                <span style="font-weight:700;font-family:'Fraunces',serif;font-size:18px">
                    {{ number_format($order->total_price, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div style="font-size:12px;color:var(--mid-gray);margin-bottom:14px">
                @if($order->is_paid)
                    ✅ <strong style="color:var(--sage)">Commande payée</strong>
                    @if($order->payment_reference)
                        <div style="margin-top:4px">Réf: {{ $order->payment_reference }}</div>
                    @endif
                @else
                    ⏳ <strong style="color:var(--terracotta)">En attente de paiement</strong>
                @endif
            </div>

            @if(!$order->is_paid && $order->status !== 'livree')
                <form method="POST" action="{{ route('client.orders.pay', $order) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="pc-btn pc-btn-primary" style="width:100%;padding:11px;justify-content:center">
                        💳 Payer maintenant
                    </button>
                </form>
            @endif
        </div>

        {{-- Paiement --}}
        <div class="pc-card" style="padding:16px;margin-bottom:14px">
            <div style="font-size:12px;text-transform:uppercase;color:var(--mid-gray);font-weight:600;margin-bottom:8px">Mode de paiement</div>
            <div style="font-weight:500">
                @if($order->payment_method === 'cash')
                    💵 Espèces
                @elseif($order->payment_method === 'mobile_money')
                    📱 Mobile Money
                @else
                    💳 Carte bancaire
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:8px;flex-direction:column">
            <a href="{{ route('menu') }}" class="pc-btn" style="flex:1;padding:10px;justify-content:center">
                Continuer les achats
            </a>
            @if(in_array($order->status, ['livree', 'prete']))
            <a href="{{ route('client.reports.create', ['order_id' => $order->id]) }}"
                class="pc-btn" style="flex:1;padding:10px;justify-content:center;border-color:#e6b2ac;color:#c0392b">
                ⚠️ Signaler un problème
            </a>
            @endif
        </div>
    </div>
</div>

@endsection
