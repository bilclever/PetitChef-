@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px">
    <div>
        <h1 class="pc-title">Menu du <em style="font-style:italic;color:var(--terracotta)">jour</em></h1>
        <p class="pc-subtitle">{{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#EFF5F0;color:var(--sage)">{{ $dishes->count() }} plat(s) disponibles</span>
    </div>
</div>

<div style="background:#EAF0FE;border:1px solid #9BB4EC;border-radius:10px;padding:12px 16px;font-size:13px;color:#2B50A0;display:flex;align-items:center;gap:9px;margin-bottom:24px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Récupération sur place ou livraison à domicile.
</div>

@if($dishes->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:var(--mid-gray)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.4"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
        Aucun plat disponible pour le moment.
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px">
        @foreach($dishes as $dish)
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
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
                    <div style="font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:var(--terracotta)">
                        {{ number_format($dish->price, 0, ',', ' ') }} FCFA
                    </div>
                    <div style="font-size:11px;color:var(--mid-gray)">par {{ $dish->cook->name }}</div>
                </div>

                @if(auth()->check() && auth()->user()->role === 'client')
                <form method="POST" action="{{ route('cart.add') }}" style="margin-top:14px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    @csrf
                    <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                    <input name="quantity" type="number" min="1" max="{{ $dish->quantity }}" value="1" style="width:70px;border:1.5px solid var(--border);border-radius:10px;padding:8px 10px;background:var(--warm-white);">
                    <button class="pc-btn pc-btn-primary" type="submit" style="padding:9px 14px;">Ajouter</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
