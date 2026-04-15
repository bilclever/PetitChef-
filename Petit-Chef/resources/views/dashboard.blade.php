@extends('layouts.app')

@section('content')
<section class="pc-card" style="padding:24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 class="pc-title">{{ $title }}</h1>
            <p class="pc-subtitle">{{ $description }}</p>
        </div>
        <div class="pc-status pc-status-{{ auth()->user()->approval_status }}">{{ ucfirst(auth()->user()->approval_status) }}</div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;">
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Profil</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ ucfirst(auth()->user()->role) }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Rôle connecté</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Commandes</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">12</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Aujourd'hui</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Revenus</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">42 100</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">FCFA estimés</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Compte</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ auth()->user()->id }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Identifiant</div>
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
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#1042</td>
                        <td>12 avr. 2026</td>
                        <td>7 800 FCFA</td>
                        <td><span class="pc-status pc-status-pending">Préparation</span></td>
                    </tr>
                    <tr>
                        <td>#1039</td>
                        <td>10 avr. 2026</td>
                        <td>3 000 FCFA</td>
                        <td><span class="pc-status pc-status-approved">Livrée</span></td>
                    </tr>
                    <tr>
                        <td>#1018</td>
                        <td>5 avr. 2026</td>
                        <td>5 600 FCFA</td>
                        <td><span class="pc-status pc-status-rejected">Annulée</span></td>
                    </tr>
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