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

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:18px;">
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Profil</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ ucfirst(auth()->user()->role) }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Rôle connecté</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Commandes</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $stats['orders_count'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Total passées</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Dépenses</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ number_format($stats['total_spent'], 0, ',', ' ') }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">FCFA dépensés</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Plats commandés</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $stats['total_dishes_ordered'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Total consommés</div>
        </div>
        <div class="pc-card" style="padding:14px;border-radius:12px;">
            <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Compte</div>
            <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;line-height:1;margin-top:4px;">{{ $stats['user_id'] }}</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Identifiant</div>
        </div>
    </div>
</section>

{{-- Menu du jour --}}
<section class="pc-card" style="padding:24px;margin-top:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div>
            <h2 style="margin:0;font-family:'Fraunces',serif;font-size:20px;font-weight:500;">Menu du <em style="font-style:italic;color:var(--terracotta)">jour</em></h2>
            <p class="pc-subtitle">{{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#EFF5F0;color:var(--sage)">{{ $dishes->count() }} plat(s) disponibles</span>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="background:#F8F9FA;border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:20px;">
        <form method="GET" action="{{ route('client.dashboard') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <div style="display:flex;align-items:center;gap:8px;">
                <label for="price_filter" style="font-size:13px;font-weight:500;">Prix max:</label>
                <input type="number" id="price_filter" name="max_price" value="{{ request('max_price') }}" placeholder="FCFA" style="width:100px;border:1.5px solid var(--border);border-radius:8px;padding:6px 8px;">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <label for="cook_filter" style="font-size:13px;font-weight:500;">Cuisinier:</label>
                <select id="cook_filter" name="cook_id" style="border:1.5px solid var(--border);border-radius:8px;padding:6px 8px;">
                    <option value="">Tous les cuisiniers</option>
                    @foreach($dishes->unique('cook_id')->pluck('cook') as $cook)
                        <option value="{{ $cook->id }}" {{ request('cook_id') == $cook->id ? 'selected' : '' }}>{{ $cook->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <label for="sort" style="font-size:13px;font-weight:500;">Trier par:</label>
                <select id="sort" name="sort" style="border:1.5px solid var(--border);border-radius:8px;padding:6px 8px;">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Plus récent</option>
                </select>
            </div>
            <button type="submit" class="pc-btn pc-btn-primary" style="padding:6px 12px;">Filtrer</button>
            <a href="{{ route('client.dashboard') }}" class="pc-btn" style="padding:6px 12px;">Réinitialiser</a>
        </form>
    </div>

    @if($dishes->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:var(--mid-gray)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.4"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
            Aucun plat disponible pour le moment.
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
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
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div style="font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:var(--terracotta)">
                            {{ number_format($dish->price, 0, ',', ' ') }} FCFA
                        </div>
                        <div style="font-size:11px;color:var(--mid-gray)">par {{ $dish->cook->name }}</div>
                    </div>

                    {{-- Ajouter au panier --}}
                    <form method="POST" action="{{ route('cart.add') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        @csrf
                        <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                        <input name="quantity" type="number" min="1" max="{{ $dish->quantity }}" value="1" style="width:70px;border:1.5px solid var(--border);border-radius:10px;padding:8px 10px;background:var(--warm-white);">
                        <button class="pc-btn pc-btn-primary" type="submit" style="padding:9px 14px;">Ajouter</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</section>

<style>
    @media (max-width: 980px) {
        section[style*='grid-template-columns:repeat(5,1fr)'] { grid-template-columns: repeat(2, 1fr) !important; }
        section[style*='grid-template-columns:1.2fr .8fr'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection