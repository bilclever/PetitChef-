@extends('layouts.app')

@section('content')
<div class="pc-card" style="max-width:980px;margin:0 auto;padding:28px 24px;">
    <div style="max-width:680px;">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.26em;color:var(--terracotta);margin:0;">Créer un compte</p>
        <h1 class="pc-title" style="margin-top:8px;">Inscription client ou cuisinier</h1>
        <p class="pc-subtitle" style="margin-top:8px;">Rejoins petitChef avec validation forte côté serveur et photo de profil optionnelle.</p>
    </div>

    <form style="margin-top:18px;display:grid;grid-template-columns:1fr 1fr;gap:12px;" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="pc-field">
            <label class="pc-label">Nom complet</label>
            <input name="name" value="{{ old('name') }}" class="pc-input" placeholder="Amara Ballo" required>
        </div>
        <div class="pc-field">
            <label class="pc-label">E-mail</label>
            <input name="email" type="email" value="{{ old('email') }}" class="pc-input" placeholder="vous@exemple.com" required>
        </div>
        <div class="pc-field">
            <label class="pc-label">Téléphone</label>
            <input name="phone" value="{{ old('phone') }}" class="pc-input" placeholder="+228XXXXXXXX ou XXXXXXXXX" required>
        </div>
        <div class="pc-field">
            <label class="pc-label">Rôle</label>
            <select name="role" class="pc-select" required>
                <option value="client" @selected(old('role', 'client') === 'client')>Client</option>
                <option value="cook" @selected(old('role') === 'cook')>Cuisinier</option>
            </select>
        </div>
        <div class="pc-field">
            <label class="pc-label">Mot de passe</label>
            <input name="password" type="password" class="pc-input" placeholder="••••••••" required>
        </div>
        <div class="pc-field">
            <label class="pc-label">Confirmation mot de passe</label>
            <input name="password_confirmation" type="password" class="pc-input" placeholder="••••••••" required>
        </div>
        <div class="pc-field" style="grid-column:1 / -1;">
            <label class="pc-label">Photo de profil (optionnelle)</label>
            <input name="profile_photo" type="file" accept="image/*" class="pc-input" style="border-style:dashed;">
            <p style="margin:4px 0 0;color:var(--mid-gray);font-size:11px;">Format image, minimum 500x500 px, max 2 Mo.</p>
        </div>
        <div class="hidden">
            <label>Website</label>
            <input name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div style="grid-column:1 / -1;display:flex;gap:10px;align-items:center;">
            <button class="pc-btn pc-btn-primary" style="width:100%;padding:12px;" type="submit">Créer le compte</button>
        </div>
    </form>

    <p style="margin:12px 0 0;font-size:12px;color:var(--mid-gray);">
        Déjà inscrit ? <a href="{{ route('login') }}" style="color:var(--terracotta);font-weight:700;text-decoration:none;">Se connecter</a>
    </p>
</div>

<style>
    @media (max-width: 760px) {
        form[style*='grid-template-columns:1fr 1fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection