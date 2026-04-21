@extends('layouts.app')

@section('content')
@php
    $clientStats = $clientStats ?? [
        'total_orders' => 0,
        'open_orders' => 0,
        'total_paid_amount' => 0,
        'unpaid_orders' => 0,
        'cart_items' => 0,
        'cart_subtotal' => 0,
    ];
@endphp

<section class="pc-card" style="padding:24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 class="pc-title">{{ $title }}</h1>
            <p class="pc-subtitle">{{ $description }}</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:flex-end;">
            <a href="{{ route('menu') }}" class="pc-btn" style="padding:6px 10px;">Voir le menu</a>
            <a href="{{ route('cart.index') }}" class="pc-btn pc-btn-primary" style="padding:6px 10px;">Ouvrir le panier</a>
            <div class="pc-status pc-status-{{ auth()->user()->approval_status }}">{{ ucfirst(auth()->user()->approval_status) }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;">
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Commandes</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $clientStats['total_orders'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Historique total</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">En cours</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $clientStats['open_orders'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Préparation / prêtes</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Total payé</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ number_format($clientStats['total_paid_amount'], 0, ',', ' ') }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">FCFA</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Panier</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $clientStats['cart_items'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Article(s) · {{ number_format($clientStats['cart_subtotal'], 0, ',', ' ') }} FCFA</div>
        </div>
    </div>
</section>

<section style="display:grid;grid-template-columns:1.2fr .8fr;gap:14px;margin-top:14px;">
    <div class="pc-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Suivi des commandes</h2>
            <a href="{{ route('profile.edit') }}" class="pc-btn" style="padding:6px 10px;">Modifier profil</a>
        </div>
        <div class="pc-table-wrap" style="border:none;border-radius:0;">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paiement</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusLabels = [
                            'recue' => 'Reçue',
                            'en_preparation' => 'En préparation',
                            'prete' => 'Prête',
                            'livree' => 'Livrée',
                            'annulee' => 'Annulée',
                        ];
                    @endphp
                    @forelse(($recentOrders ?? collect()) as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format((int) $order->total_price, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @if ($order->is_paid)
                                    <span style="font-size:11px;color:var(--sage);font-weight:600">Payée</span>
                                @else
                                    <span style="font-size:11px;color:var(--terracotta);font-weight:600">En attente</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap">
                                    @php
                                        $statusClass = in_array($order->status, ['livree'], true)
                                            ? 'approved'
                                            : (in_array($order->status, ['annulee'], true) ? 'rejected' : 'pending');
                                    @endphp
                                    <span class="pc-status pc-status-{{ $statusClass }}" data-order-status="{{ $order->id }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                                    @if (! $order->is_paid)
                                        <form method="POST" action="{{ route('client.orders.pay', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="pc-btn" style="padding:4px 8px;font-size:11px">Payer</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="color:var(--mid-gray);text-align:center;">Aucune commande pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <aside class="pc-card" style="padding:16px;">
        <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Compte connecté</h2>
        <p class="pc-subtitle" style="margin-top:4px;">Informations du profil actif</p>
        <div style="margin-top:10px;font-size:13px;display:grid;gap:8px;">
            <div><strong>Nom:</strong> {{ auth()->user()->name }}</div>
            <div><strong>Email:</strong> {{ auth()->user()->email }}</div>
            <div><strong>Rôle:</strong> {{ ucfirst(auth()->user()->role) }}</div>
            <div><strong>Téléphone:</strong> {{ auth()->user()->phone }}</div>
        </div>

        @if (auth()->user()->profile_photo_url)
            <div style="margin-top:12px;">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="Photo de profil" style="height:120px;width:120px;border-radius:16px;object-fit:cover;border:4px solid #f1e3d3;">
            </div>
        @endif
    </aside>
</section>

<style>
    @media (max-width: 980px) {
        section[style*='grid-template-columns:repeat(4,1fr)'] { grid-template-columns: repeat(2, 1fr) !important; }
        section[style*='grid-template-columns:1.2fr .8fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection