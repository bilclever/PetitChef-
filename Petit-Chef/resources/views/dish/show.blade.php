@extends('layouts.app')

@section('content')

{{-- Retour --}}
<div style="margin-bottom:20px">
    <a href="{{ route('menu') }}" style="color:var(--terracotta);text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:6px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><polyline points="15 18 9 12 15 6"/></svg>
        Retour au menu
    </a>
</div>

{{-- Détail du plat --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:36px;align-items:start">

    {{-- Image --}}
    <div style="border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#F5DEB3,#DEB887);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;position:relative">
        @if($dish->photo_path)
            <img src="{{ asset('storage/'.$dish->photo_path) }}" style="width:100%;height:100%;object-fit:cover" alt="{{ $dish->name }}">
        @else
            <span style="font-size:96px">{{ $dish->emoji ?? '🍽️' }}</span>
        @endif
        @if($dish->is_of_day)
            <div style="position:absolute;top:14px;left:14px;background:var(--terracotta);color:#fff;border-radius:20px;padding:4px 12px;font-size:11px;font-weight:700">
                ⭐ Plat du jour
            </div>
        @endif
        @if(! $isOpen)
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center">
                <span style="background:#c0392b;color:#fff;border-radius:20px;padding:8px 20px;font-size:14px;font-weight:700">🔴 Boutique fermée</span>
            </div>
        @endif
    </div>

    {{-- Infos --}}
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <span style="font-size:12px;color:var(--mid-gray)">par</span>
            <a href="{{ route('dish.show', $dish) }}" style="font-weight:600;color:var(--charcoal);text-decoration:none;font-size:14px">
                {{ $dish->cook->name }}
            </a>
            <span style="width:7px;height:7px;border-radius:50%;background:{{ $isOpen ? '#2ecc71' : '#c0392b' }};display:inline-block"></span>
            <span style="font-size:11px;color:{{ $isOpen ? 'var(--sage)' : '#c0392b' }};font-weight:600">
                {{ $isOpen ? 'Ouvert' : 'Fermé' }}
            </span>
        </div>

        <h1 class="pc-title" style="margin:0 0 10px">{{ $dish->name }}</h1>

        @if($dish->description)
            <p style="color:var(--mid-gray);font-size:14px;line-height:1.7;margin:0 0 20px">{{ $dish->description }}</p>
        @endif

        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
            <div style="font-family:'Fraunces',serif;font-size:32px;font-weight:700;color:var(--terracotta)">
                {{ number_format($dish->price, 0, ',', ' ') }} <span style="font-size:16px">FCFA</span>
            </div>
            <div style="font-size:13px;padding:4px 12px;border-radius:20px;background:{{ $dish->quantity > 3 ? '#EFF5F0' : '#FEF0EA' }};color:{{ $dish->quantity > 3 ? 'var(--sage)' : 'var(--terracotta)' }};font-weight:600">
                {{ $dish->quantity }} restant(s)
            </div>
        </div>

        @if(auth()->user()->role === 'client')
            @if($isOpen && $dish->quantity > 0)
                <form method="POST" action="{{ route('cart.add', $dish) }}" style="display:flex;gap:10px;align-items:center">
                    @csrf
                    <input type="number" name="quantity" class="pc-input" min="1" max="{{ $dish->quantity }}" value="1" style="width:90px;padding:10px 12px;font-size:15px">
                    <button type="submit" class="pc-btn pc-btn-primary" style="padding:11px 24px;font-size:14px">
                        🛒 Ajouter au panier
                    </button>
                </form>
            @elseif(! $isOpen)
                <div style="padding:12px 16px;background:#FFF5F5;border:1px solid #FFCDD2;border-radius:10px;color:#c0392b;font-size:13px">
                    🔴 Ce cuisinier a clôturé ses commandes pour le moment.
                </div>
            @else
                <div style="padding:12px 16px;background:#FEF0EA;border-radius:10px;color:var(--terracotta);font-size:13px;font-weight:600">
                    Épuisé pour aujourd'hui.
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Catalogue du cuisinier --}}
<div style="border-top:1px solid var(--border);padding-top:28px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div>
            <h2 style="font-family:'Fraunces',serif;font-size:22px;font-weight:500;margin:0">
                Autres plats de <em style="color:var(--terracotta)">{{ $dish->cook->name }}</em>
            </h2>
            <p class="pc-subtitle" style="margin-top:4px">{{ $cookDishes->count() }} plat(s) disponibles</p>
        </div>
    </div>

    @if($cookDishes->isEmpty())
        <div style="text-align:center;padding:32px;color:var(--mid-gray);border:2px dashed var(--border);border-radius:12px">
            Aucun autre plat disponible pour le moment.
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
            @foreach($cookDishes as $other)
            <a href="{{ route('dish.show', $other) }}" style="text-decoration:none;color:inherit">
            <div style="background:var(--warm-white);border:1px solid {{ $other->id === $dish->id ? 'var(--terracotta)' : 'var(--border)' }};border-radius:12px;overflow:hidden;transition:box-shadow .18s,transform .18s"
                onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'"
                onmouseout="this.style.boxShadow='';this.style.transform=''">

                {{-- Image --}}
                <div style="height:130px;position:relative;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#F5DEB3,#DEB887)">
                    @if($other->photo_path)
                        <img src="{{ asset('storage/'.$other->photo_path) }}" style="width:100%;height:100%;object-fit:cover" alt="{{ $other->name }}">
                    @else
                        <span style="font-size:40px">{{ $other->emoji ?? '🍽️' }}</span>
                    @endif
                    @if($other->id === $dish->id)
                        <div style="position:absolute;top:8px;left:8px;background:var(--terracotta);color:#fff;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:700">
                            Sélectionné
                        </div>
                    @endif
                    @if($other->is_of_day)
                        <div style="position:absolute;top:8px;right:8px;background:var(--terracotta);color:#fff;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:700">⭐</div>
                    @endif
                </div>

                {{-- Infos --}}
                <div style="padding:12px 14px">
                    <div style="font-weight:600;font-size:14px;margin-bottom:4px">{{ $other->name }}</div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div style="font-family:'Fraunces',serif;font-size:15px;font-weight:700;color:var(--terracotta)">
                            {{ number_format($other->price, 0, ',', ' ') }} F
                        </div>
                        <div style="font-size:11px;color:var(--mid-gray)">{{ $other->quantity }} restants</div>
                    </div>
                </div>
            </div>
            </a>
            @endforeach
        </div>
    @endif
</div>

<style>
    @media (max-width: 760px) {
        div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
