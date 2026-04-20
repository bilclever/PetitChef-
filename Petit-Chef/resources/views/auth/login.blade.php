@extends('layouts.app')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;max-width:980px;margin:0 auto;">
    <section class="pc-card" style="background:var(--charcoal);padding:34px 30px;color:var(--cream);position:relative;overflow:hidden;">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.28em;color:#f4c8b6;margin:0;">Authentification</p>
        <h1 style="font-family:'Fraunces',serif;font-size:34px;font-weight:500;line-height:1.2;margin:14px 0 0;">Connexion sécurisée à petitChef</h1>
        <p style="margin-top:10px;max-width:520px;font-size:13px;color:rgba(249,245,238,.72);">
            Accède à ton espace selon ton rôle. Le système applique la redirection automatique vers client, cuisinier ou administrateur.
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;">
            <div style="border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);border-radius:10px;padding:11px 12px;font-size:12px;color:#f3d8c9;">Validation forte des identifiants</div>
            <div style="border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);border-radius:10px;padding:11px 12px;font-size:12px;color:#f3d8c9;">Middleware de rôle protégé</div>
            <div style="border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);border-radius:10px;padding:11px 12px;font-size:12px;color:#f3d8c9;">Profil et photo modifiables</div>
            <div style="border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);border-radius:10px;padding:11px 12px;font-size:12px;color:#f3d8c9;">Dashboard adapté au compte</div>
        </div>
    </section>

    <section class="pc-card" style="padding:32px 26px;">
        <h2 style="font-family:'Fraunces',serif;font-size:24px;font-weight:500;margin:0;">Se connecter</h2>
        <p class="pc-subtitle">Bon retour. Continue ton service ou tes commandes.</p>
        <form style="display:flex;flex-direction:column;gap:12px;margin-top:16px;" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="pc-field">
                <label class="pc-label">Adresse e-mail</label>
                <input name="email" type="email" value="{{ old('email') }}" class="pc-input" placeholder="vous@exemple.com" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Mot de passe</label>
                <input name="password" type="password" class="pc-input" placeholder="••••••••" required>
            </div>
            <div class="hidden">
                <label>Website</label>
                <input name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--mid-gray);">
                <input type="checkbox" name="remember" value="1">
                Se souvenir de moi
            </label>
            <button type="submit" class="pc-btn pc-btn-primary" style="width:100%;padding:11px;">Connexion</button>
        </form>
        <p style="margin-top:14px;font-size:12px;color:var(--mid-gray);">
            Pas encore de compte ? <a href="{{ route('register') }}" style="color:var(--terracotta);font-weight:700;text-decoration:none;">Créer un compte</a>
        </p>
    </section>
</div>

<style>
    @media (max-width: 900px) {
        div[style*='grid-template-columns:1fr 1fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection