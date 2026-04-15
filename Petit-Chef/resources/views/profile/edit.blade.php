@extends('layouts.app')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
    <section class="pc-card" style="padding:20px;">
        <h1 style="margin:0;font-family:'Fraunces',serif;font-size:24px;font-weight:500;">Modifier le profil</h1>
        <p class="pc-subtitle">Nom, e-mail, téléphone et photo.</p>
        <form style="margin-top:12px;display:flex;flex-direction:column;gap:10px;" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="pc-field">
                <label class="pc-label">Nom</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="pc-input" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="pc-input" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Téléphone</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="pc-input" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Nouvelle photo</label>
                <input name="profile_photo" type="file" accept="image/*" class="pc-input" style="border-style:dashed;">
            </div>
            <button class="pc-btn pc-btn-primary" type="submit" style="width:100%;padding:11px;">Enregistrer</button>
        </form>
    </section>

    <section class="pc-card" style="padding:20px;">
        <h2 style="margin:0;font-family:'Fraunces',serif;font-size:24px;font-weight:500;">Sécurité du compte</h2>
        <p class="pc-subtitle">Mettre à jour le mot de passe actuel.</p>
        <form style="margin-top:12px;display:flex;flex-direction:column;gap:10px;" method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="pc-field">
                <label class="pc-label">Mot de passe actuel</label>
                <input name="current_password" type="password" class="pc-input" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Nouveau mot de passe</label>
                <input name="password" type="password" class="pc-input" required>
            </div>
            <div class="pc-field">
                <label class="pc-label">Confirmation</label>
                <input name="password_confirmation" type="password" class="pc-input" required>
            </div>
            <button class="pc-btn pc-btn-primary" type="submit" style="width:100%;padding:11px;">Mettre à jour</button>
        </form>

        <div style="margin-top:14px;padding:10px;border-radius:10px;background:#f8efe5;border:1px solid var(--border);font-size:13px;">
            Statut actuel: <span class="pc-status pc-status-{{ $user->approval_status }}">{{ ucfirst($user->approval_status) }}</span>
        </div>

        @if ($user->profile_photo_url)
            <div style="margin-top:12px;">
                <p class="pc-label" style="margin-bottom:4px;">Photo actuelle</p>
                <img src="{{ $user->profile_photo_url }}" alt="Photo de profil" style="height:120px;width:120px;border-radius:16px;object-fit:cover;border:4px solid #f1e3d3;">
            </div>
        @endif
    </section>
</div>

<style>
    @media (max-width: 860px) {
        div[style*='grid-template-columns:1fr 1fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection