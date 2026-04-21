@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px">
    <div>
        <h1 class="pc-title">Menu du <em style="font-style:italic;color:var(--terracotta)">jour</em></h1>
        <p class="pc-subtitle">{{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#EFF5F0;color:var(--sage)">
            {{ $dishes->count() }} plat(s) disponibles
        </span>
        @if(auth()->user()->role === 'client')
        <a href="{{ route('cart.index') }}" class="pc-btn" style="padding:6px 12px">Mon panier</a>
        @endif
    </div>
</div>

@if($dishes->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:var(--mid-gray)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.4">
            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
        </svg>
        Aucun plat disponible pour le moment.
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px">
        @foreach($dishes as $dish)
        @php $isOpen = $dish->cook ? $dish->cook->isShopOpen() : false; @endphp
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:box-shadow .18s,transform .18s;{{ $isOpen ? '' : 'opacity:.55' }}"
            onmouseover="{{ $isOpen ? "this.style.boxShadow='0 6px 24px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'" : '' }}"
            onmouseout="this.style.boxShadow='';this.style.transform=''">

            {{-- Image --}}
            <a href="{{ route('dish.show', $dish) }}" style="display:block;text-decoration:none">
            <div style="height:160px;position:relative;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#F5DEB3,#DEB887)">
                @if($dish->photo_path)
                    <img src="{{ asset('storage/'.$dish->photo_path) }}" style="width:100%;height:100%;object-fit:cover" alt="{{ $dish->name }}">
                @else
                    <span style="font-size:52px">{{ $dish->emoji ?? '🍽️' }}</span>
                @endif

                {{-- Badge stock --}}
                <div style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,.9);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;border:1px solid var(--border);{{ $dish->quantity <= 3 ? 'color:var(--terracotta);border-color:var(--terracotta)' : '' }}">
                    {{ $dish->quantity }} restants
                </div>

                {{-- Badge plat du jour --}}
                @if($dish->is_of_day)
                <div style="position:absolute;top:10px;left:10px;background:var(--terracotta);color:#fff;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700">
                    ⭐ Plat du jour
                </div>
                @endif

                {{-- Badge fermé --}}
                @if(! $isOpen)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center">
                    <span style="background:#c0392b;color:#fff;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700">🔴 Fermé</span>
                </div>
                @endif
            </div>
            </a>{{-- fin lien image --}}

            {{-- Infos --}}
            <div style="padding:14px 16px">
                <a href="{{ route('dish.show', $dish) }}" style="text-decoration:none;color:inherit">
                    <div style="font-family:'Fraunces',serif;font-size:16px;font-weight:500;margin-bottom:4px">{{ $dish->name }}</div>
                </a>

                @if($dish->description)
                <div style="font-size:12px;color:var(--mid-gray);line-height:1.5;margin-bottom:8px">{{ Str::limit($dish->description, 80) }}</div>
                @endif

                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px">
                    <div style="font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:var(--terracotta)">
                        {{ number_format($dish->price, 0, ',', ' ') }} FCFA
                    </div>
                    {{-- Nom du cuisinier avec statut --}}
                    <div style="font-size:11px;color:var(--mid-gray);display:flex;align-items:center;gap:4px">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $isOpen ? '#2ecc71' : '#c0392b' }};display:inline-block;flex-shrink:0"></span>
                        {{ $dish->cook->name }}
                    </div>
                </div>

                @if(auth()->user()->role === 'client')
                <form method="POST" action="{{ route('cart.add', $dish) }}" style="display:flex;gap:8px;align-items:center;margin-top:12px">
                    @csrf
                    <input type="number" name="quantity" class="pc-input" min="1" max="{{ $dish->quantity }}" value="1"
                        style="width:78px;padding:7px 9px" @disabled(! $isOpen || $dish->quantity < 1)>
                    <button type="submit" class="pc-btn pc-btn-primary" style="padding:7px 11px;flex:1;justify-content:center"
                        @disabled(! $isOpen || $dish->quantity < 1)>
                        {{ ! $isOpen ? 'Fermé' : ($dish->quantity < 1 ? 'Épuisé' : 'Ajouter') }}
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
