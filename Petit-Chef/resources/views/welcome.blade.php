@extends('layouts.app')

@section('content')
    <div class="pc-card" style="max-width:900px;margin:0 auto;padding:30px 28px;">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.26em;color:var(--terracotta);margin:0;">Projet</p>
        <h1 class="pc-title" style="margin-top:8px;">petitChef — commande de repas maison</h1>
        <p class="pc-subtitle" style="max-width:680px;">
            Une plateforme pour clients, cuisiniers et administrateurs: menu du jour, suivi de commandes, et gestion des profils.
        </p>

        <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px;">
            <a href="{{ route('login') }}" class="pc-btn pc-btn-primary">Connexion</a>
            <a href="{{ route('register') }}" class="pc-btn">Inscription</a>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
            <div class="pc-card" style="padding:12px;border-radius:12px;">Nom validé: lettres, espaces, tirets, apostrophes</div>
            <div class="pc-card" style="padding:12px;border-radius:12px;">Téléphone: 9 chiffres ou +228 suivi de 8 chiffres</div>
            <div class="pc-card" style="padding:12px;border-radius:12px;">Mot de passe robuste: minuscule, majuscule, chiffre, symbole</div>
        </div>
    </div>

    <style>
        @media (max-width: 760px) {
            div[style*='grid-template-columns:1fr 1fr 1fr'] { grid-template-columns: 1fr !important; }
        }
    </style>
@endsection
