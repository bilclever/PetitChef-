@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px">
    <div>
        <h1 class="pc-title">Panier</h1>
        <p class="pc-subtitle">Revérifie les quantités et passe ta commande.</p>
    </div>
</div>

@if($items->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:var(--mid-gray)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.4"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
        Ton panier est vide.
    </div>
@else
    <div class="pc-card" style="padding:20px;">
        <table class="pc-table">
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php($total = 0)
                @foreach($items as $item)
                    @php($total += $item['subtotal'])
                <tr>
                    <td>{{ $item['dish']->name }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ number_format($item['dish']->price, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</td>
                    <td>
                        <form method="POST" action="{{ route('cart.remove') }}">
                            @csrf
                            <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                            <button class="pc-btn" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:700">Total</td>
                    <td style="font-weight:700">{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;display:grid;grid-template-columns:1fr 320px;gap:18px;">
        <div class="pc-card" style="padding:18px;">
            <form method="POST" action="{{ route('cart.checkout') }}">
                @csrf

                <div class="pc-field" style="margin-bottom:14px;">
                    <label class="pc-label" for="delivery_address">Adresse de livraison (optionnel)</label>
                    <input class="pc-input" id="delivery_address" name="delivery_address" type="text" value="{{ old('delivery_address') }}">
                </div>

                <div class="pc-field" style="margin-bottom:14px;">
                    <label class="pc-label" for="note">Note pour le cuisinier</label>
                    <textarea class="pc-textarea" id="note" name="note">{{ old('note') }}</textarea>
                </div>

                <button class="pc-btn pc-btn-primary" type="submit" style="width:100%;">Valider la commande</button>
            </form>
        </div>

        <div class="pc-card" style="padding:18px;">
            <div style="font-size:12px;color:var(--mid-gray);margin-bottom:10px;">Résumé</div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span>Nombre de plats</span><strong>{{ $items->sum('quantity') }}</strong></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span>Montant total</span><strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></div>
            <p style="color:var(--mid-gray);font-size:13px;margin-top:12px;">Une fois validée, ta commande sera enregistrée et le stock diminué immédiatement.</p>
        </div>
    </div>
@endif
@endsection
