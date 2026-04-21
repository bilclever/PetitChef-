@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px">
    <div>
        <h1 class="pc-title">Mon <em style="font-style:italic;color:var(--terracotta)">panier</em></h1>
        <p class="pc-subtitle">{{ $itemsCount }} article(s) · commande chez un seul cuisinier</p>
    </div>
    <a href="{{ route('menu') }}" class="pc-btn">Retour au menu</a>
</div>

@if ($cart['items'] === [])
    <div class="pc-card" style="padding:26px;text-align:center;color:var(--mid-gray)">
        Ton panier est vide. Ajoute des plats depuis le menu du jour.
    </div>
@else
    @php
        // Vérifier si le cuisinier du panier a fermé sa boutique
        $cartCookId = $cart['cook_id'] ?? null;
        $cartCookClosed = false;
        if ($cartCookId) {
            $cartCook = \App\Models\User::find($cartCookId);
            $cartCookClosed = $cartCook && ! $cartCook->isShopOpen();
        }
    @endphp

    @if($cartCookClosed)
    <div style="background:#FFF5F5;border:1px solid #FFCDD2;border-radius:10px;padding:14px 16px;font-size:13px;color:#c0392b;margin-bottom:16px;display:flex;align-items:center;gap:10px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <strong>Boutique fermée</strong> — Ce cuisinier a clôturé ses commandes.
            Tu peux vider ton panier et commander chez un autre cuisinier.
        </div>
    </div>
    @endif
    <section style="display:grid;grid-template-columns:1.25fr .75fr;gap:16px;align-items:start">
        <div class="pc-card" style="padding:0;overflow:hidden">
            <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
                <h2 style="margin:0;font-family:'Fraunces',serif;font-size:19px;font-weight:500">Articles</h2>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pc-btn" style="padding:6px 10px">Vider le panier</button>
                </form>
            </div>

            <div class="pc-table-wrap" style="border:none;border-radius:0">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Prix</th>
                            <th>Quantite</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cart['items'] as $item)
                            @php
                                $lineTotal = ((int) $item['price']) * ((int) $item['quantity']);
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <span style="font-size:20px">{{ $item['emoji'] ?: '🍽️' }}</span>
                                        <div>
                                            <div style="font-weight:600">{{ $item['name'] }}</div>
                                            <div style="font-size:12px;color:var(--mid-gray)">par {{ $item['cook_name'] ?: 'Cuisinier' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format((int) $item['price'], 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <form method="POST" action="{{ route('cart.update', $item['dish_id']) }}" style="display:flex;gap:6px;align-items:center">
                                        @csrf
                                        @method('PATCH')
                                        <input class="pc-input" name="quantity" type="number" min="0" max="20" value="{{ (int) $item['quantity'] }}" style="width:74px;padding:6px 8px">
                                        <button type="submit" class="pc-btn" style="padding:6px 10px">OK</button>
                                    </form>
                                </td>
                                <td><strong>{{ number_format($lineTotal, 0, ',', ' ') }} FCFA</strong></td>
                                <td>
                                    <form method="POST" action="{{ route('cart.remove', $item['dish_id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pc-btn" style="padding:6px 10px">Retirer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="pc-card" style="padding:16px">
            <h2 style="margin:0;font-family:'Fraunces',serif;font-size:19px;font-weight:500">Validation</h2>
            <p class="pc-subtitle">Creation de commande avec transaction securisee.</p>

            <div style="margin-top:12px;font-size:13px;display:grid;gap:8px">
                <div style="display:flex;justify-content:space-between;gap:8px">
                    <span style="color:var(--mid-gray)">Sous-total</span>
                    <strong>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('client.orders.store') }}" style="display:grid;gap:10px;margin-top:14px">
                @csrf
                <div class="pc-field">
                    <label class="pc-label" for="pickup_time">Heure souhaitée</label>
                    <input class="pc-input" type="datetime-local" id="pickup_time" name="pickup_time"
                        value="{{ old('pickup_time') }}"
                        min="{{ now()->addMinutes(15)->format('Y-m-d\TH:i') }}">
                    <span style="font-size:11px;color:var(--mid-gray)">Minimum 15 min à partir de maintenant</span>
                </div>

                <div class="pc-field">
                    <label class="pc-label" for="fulfillment_type">Mode de recuperation</label>
                    <select class="pc-select" id="fulfillment_type" name="fulfillment_type">
                        <option value="pickup" @selected(old('fulfillment_type') === 'pickup')>🏪 Pickup (sur place)</option>
                        <option value="delivery" @selected(old('fulfillment_type') === 'delivery')>🚚 Livraison</option>
                    </select>
                </div>

                <div class="pc-field">
                    <label class="pc-label" for="payment_method">Paiement</label>
                    <select class="pc-select" id="payment_method" name="payment_method">
                        <option value="cash" @selected(old('payment_method') === 'cash')>💵 Cash</option>
                        <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>📱 Mobile Money</option>
                        <option value="card" @selected(old('payment_method') === 'card')>💳 Carte</option>
                    </select>
                </div>

                <button type="submit" class="pc-btn pc-btn-primary" style="width:100%;justify-content:center;{{ $cartCookClosed ? 'opacity:.5;cursor:not-allowed' : '' }}"
                    onclick="this.disabled=true;this.textContent='Traitement…';this.form.submit()"
                    @disabled($cartCookClosed)>
                    {{ $cartCookClosed ? '🔴 Boutique fermée' : 'Confirmer la commande' }}
                </button>
            </form>
        </aside>
    </section>
@endif

<style>
    @media (max-width: 900px) {
        section[style*='grid-template-columns:1.25fr .75fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
