@extends('layouts.app')

@section('content')
<div style="margin-top: 0; padding-top: 0;">

@if(!$items->isEmpty())
    {{-- Résumé du panier --}}
    <div style="max-width:600px;margin:0 auto 40px;">
        <div class="pc-card" style="padding:20px;">
            <h3 style="margin:0 0 16px;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Votre commande</h3>

            <div style="max-height:300px;overflow-y:auto;margin-bottom:16px;">
                @php($total = 0)
                @foreach($items as $item)
                    @php($total += $item['subtotal'])
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
                        <div style="flex:1;">
                            <div style="font-weight:500;font-size:14px;">{{ $item['dish']->name }}</div>
                            <div style="font-size:12px;color:var(--mid-gray);">{{ $item['quantity'] }} × {{ number_format($item['dish']->price, 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div style="font-weight:600;color:var(--terracotta);">{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</div>
                    </div>
                @endforeach
            </div>

            <div style="border-top:2px solid var(--border);padding-top:12px;">
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;margin-bottom:16px;">
                    <span>Total</span>
                    <span style="color:var(--terracotta);">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                </div>

                <form method="POST" action="{{ route('cart.checkout') }}">
                    @csrf

                    <div class="pc-field" style="margin-bottom:12px;">
                        <label class="pc-label" for="delivery_address">Adresse de livraison (optionnel)</label>
                        <input class="pc-input" id="delivery_address" name="delivery_address" type="text" value="{{ old('delivery_address') }}" placeholder="Votre adresse...">
                    </div>

                    <div class="pc-field" style="margin-bottom:16px;">
                        <label class="pc-label" for="note">Note pour le cuisinier</label>
                        <textarea class="pc-textarea" id="note" name="note" placeholder="Instructions spéciales...">{{ old('note') }}</textarea>
                    </div>

                    <button class="pc-btn pc-btn-primary" type="submit" style="width:100%;padding:12px;">Valider la commande</button>
                </form>
            </div>
        </div>

        {{-- Lien vers la gestion détaillée du panier --}}
        <div style="text-align:center;margin-top:20px;">
            <a href="#panier-detail" class="pc-btn pc-btn-secondary" style="padding:8px 16px;font-size:12px;" onclick="document.getElementById('panier-detail').scrollIntoView({behavior:'smooth'})">
                Gérer le panier
            </a>
        </div>
    </div>

    {{-- Section : Ajouter d'autres plats --}}
    <div style="margin-bottom:40px;">
        <h2 style="margin:0 0 16px;font-family:'Fraunces',serif;font-size:20px;font-weight:500;">Ajouter d'autres plats</h2>

        @if($availableDishes->isEmpty())
            <div style="text-align:center;padding:40px 20px;color:var(--mid-gray);background:#F8F9FA;border-radius:12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;margin:0 auto 8px;display:block;opacity:.4"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                Aucun plat disponible pour le moment.
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
                @foreach($availableDishes as $dish)
                <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:box-shadow .18s,transform .18s" onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">

                    {{-- Image / placeholder --}}
                    <div style="height:160px;position:relative;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#F5DEB3,#DEB887)">
                        @if($dish->photo_path)
                            <img src="{{ asset('storage/'.$dish->photo_path) }}" style="width:100%;height:100%;object-fit:cover">
                        @else
                            <span style="font-size:52px">{{ $dish->emoji ?? '🍽️' }}</span>
                        @endif
                        <div style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,.9);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;border:1px solid var(--border);{{ $dish->quantity <= 3 ? 'color:var(--terracotta);border-color:var(--terracotta)' : '' }}">
                            {{ $dish->quantity }} restants
                        </div>
                        @if($dish->is_of_day)
                        <div style="position:absolute;top:10px;left:10px;background:var(--terracotta);color:#fff;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700">
                            ⭐ Plat du jour
                        </div>
                        @endif
                    </div>

                    {{-- Infos --}}
                    <div style="padding:14px 16px">
                        <div style="font-family:'Fraunces',serif;font-size:16px;font-weight:500;margin-bottom:4px">{{ $dish->name }}</div>
                        @if($dish->description)
                            <div style="font-size:12px;color:var(--mid-gray);line-height:1.5;margin-bottom:12px">{{ Str::limit($dish->description, 80) }}</div>
                        @endif
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                            <div style="font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:var(--terracotta)">
                                {{ number_format($dish->price, 0, ',', ' ') }} FCFA
                            </div>
                            <div style="font-size:11px;color:var(--mid-gray)">par {{ $dish->cook->name }}</div>
                        </div>

                        {{-- Ajouter au panier --}}
                        <form method="POST" action="{{ route('cart.add') }}" style="display:flex;gap:8px;align-items:center;">
                            @csrf
                            <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                            <input name="quantity" type="number" min="1" max="{{ $dish->quantity }}" value="1" style="width:70px;border:1.5px solid var(--border);border-radius:10px;padding:8px 10px;background:var(--warm-white);">
                            <button class="pc-btn pc-btn-primary" type="submit" style="padding:9px 14px;">Ajouter</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <div style="text-align:center;margin-top:20px;">
                <a href="{{ route('menu') }}" class="pc-btn pc-btn-secondary" style="padding:10px 20px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;margin-right:6px;">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Voir le menu complet
                </a>
            </div>
        @endif
    </div>

    {{-- Section détaillée du panier --}}
    <div id="panier-detail" style="margin-top:60px;border-top:1px solid var(--border);padding-top:24px;">
        <h2 style="margin:0 0 20px;font-family:'Fraunces',serif;font-size:20px;font-weight:500;">Détails du panier</h2>

        <div class="pc-card" style="padding:20px;">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Plat</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Sous-total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php($total = 0)
                    @foreach($items as $item)
                        @php($total += $item['subtotal'])
                    <tr>
                        <td>{{ $item['dish']->name }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <form method="POST" action="{{ route('cart.update') }}" style="display:flex;align-items:center;gap:4px;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                                    <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                    <button type="submit" class="pc-btn" style="padding:2px 8px;font-size:12px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                </form>
                                <span style="font-weight:500;min-width:20px;text-align:center;">{{ $item['quantity'] }}</span>
                                <form method="POST" action="{{ route('cart.update') }}" style="display:flex;align-items:center;gap:4px;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                                    <input type="hidden" name="quantity" value="{{ min($item['dish']->quantity, $item['quantity'] + 1) }}">
                                    <button type="submit" class="pc-btn" style="padding:2px 8px;font-size:12px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;" {{ $item['quantity'] >= $item['dish']->quantity ? 'disabled' : '' }}>+</button>
                                </form>
                            </div>
                        </td>
                        <td>{{ number_format($item['dish']->price, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                                <button class="pc-btn pc-btn-danger" type="submit" style="padding:6px 12px;font-size:12px;" onclick="return confirm('Êtes-vous sûr de vouloir retirer ce plat du panier ?')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;margin-right:4px;">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Retirer
                                </button>
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
    </div>
@endif
</div>
