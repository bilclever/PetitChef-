@extends('layouts.app')

@section('content')
<div style="max-width:680px;margin:0 auto">

    <div style="margin-bottom:24px">
        <a href="{{ route('client.reports.index') }}" style="color:var(--terracotta);text-decoration:none;font-size:13px">← Mes signalements</a>
        <h1 class="pc-title" style="margin-top:8px">Nouveau <em style="font-style:italic;color:var(--terracotta)">signalement</em></h1>
        <p class="pc-subtitle">Signalez un problème à l'administrateur. Votre signalement sera traité dans les meilleurs délais.</p>
    </div>

    <div class="pc-card" style="padding:28px">
        <form method="POST" action="{{ route('client.reports.store') }}" style="display:flex;flex-direction:column;gap:16px">
            @csrf

            {{-- Commande liée --}}
            <div class="pc-field">
                <label class="pc-label" for="order_id">Commande concernée *</label>
                <select name="order_id" id="order_id" class="pc-select" required>
                    <option value="">— Sélectionner une commande —</option>
                    @foreach($orders as $item)
                        <option value="{{ $item->id }}" @selected((string) old('order_id', $order?->id) === (string) $item->id)>
                            #{{ $item->id }} · {{ $item->created_at?->format('d/m/Y H:i') }} · {{ number_format($item->total_price, 0, ',', ' ') }} F · {{ $item->cook->name ?? 'Cuisinier' }}
                        </option>
                    @endforeach
                </select>
                @error('order_id')
                    <span style="font-size:12px;color:#c0392b">{{ $message }}</span>
                @enderror
            </div>

            {{-- Type --}}
            <div class="pc-field">
                <label class="pc-label" for="type">Type de signalement *</label>
                <select name="type" id="type" class="pc-select" required>
                    <option value="">— Choisir un type —</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span style="font-size:12px;color:#c0392b">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description --}}
            <div class="pc-field">
                <label class="pc-label" for="description">Description *</label>
                <textarea name="description" id="description" class="pc-textarea"
                    placeholder="Décrivez le problème en détail (minimum 20 caractères)…"
                    style="min-height:140px" required>{{ old('description') }}</textarea>
                @error('description')
                    <span style="font-size:12px;color:#c0392b">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <a href="{{ route('client.reports.index') }}" class="pc-btn">Annuler</a>
                <button type="submit" class="pc-btn pc-btn-primary" style="padding:10px 20px">
                    Envoyer le signalement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
