@extends('layouts.app')

@section('content')
<div style="margin-bottom:24px">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
            <h1 class="pc-title">Mes <em style="font-style:italic;color:var(--terracotta)">commandes</em></h1>
            <p class="pc-subtitle">{{ $orders->total() }} commande(s) trouvée(s)</p>
        </div>
        <a href="{{ route('menu') }}" class="pc-btn">Retour au menu</a>
    </div>
</div>

@if($orders->isEmpty())
    <div class="pc-card" style="padding:40px;text-align:center;color:var(--mid-gray)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 16px;display:block;opacity:.5">
            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
        </svg>
        Tu n'as pas encore passé de commande. <a href="{{ route('menu') }}" style="color:var(--terracotta);font-weight:600">Explore le menu</a>
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:12px">
        @foreach($orders as $order)
            <a href="{{ route('client.orders.show', $order) }}" style="text-decoration:none;color:inherit">
            <div class="pc-card" style="padding:16px;display:flex;justify-content:space-between;align-items:center;cursor:pointer;transition:all .2s" onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.08)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                        <strong style="font-family:'Fraunces',serif;font-size:16px">#{{ $order->id }}</strong>
                        <span data-order-status="{{ $order->id }}" style="padding:3px 10px;font-size:11px;background:
                            @if($order->status === 'livree') #EFF5F0;color:var(--sage)
                            @elseif($order->status === 'annulee') #FFE4E1;color:#c0392b
                            @elseif($order->status === 'prete') #EAF0FE;color:#3B6FD4
                            @else #FEF0EA;color:var(--terracotta)
                            @endif
                        ;border-radius:4px;font-weight:600;font-size:11px;padding:3px 10px;display:inline-block">
                            {{ str_replace('_', ' ', ucfirst($order->status)) }}
                        </span>
                        @if(!$order->is_paid)
                            <span style="padding:3px 10px;font-size:11px;background:#FFE4E1;color:#c0392b;border-radius:4px;font-weight:600">À payer</span>
                        @endif
                    </div>
                    <div style="font-size:13px;color:var(--mid-gray)">
                        {{ $order->created_at->isoFormat('D MMMM YYYY à HH:mm') }} — {{ $order->dishes->sum(fn($d) => $d->pivot->quantity) }} article(s)
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-family:'Fraunces',serif;font-size:18px;font-weight:700;color:var(--terracotta)">
                        {{ number_format($order->total_price, 0, ',', ' ') }} F
                    </div>
                    <div style="font-size:11px;color:var(--mid-gray);margin-top:4px">
                        @if($order->fulfillment_type === 'pickup')
                            🏪 Sur place
                        @else
                            🚚 Livraison
                        @endif
                    </div>
                </div>
            </div>
            </a>
        @endforeach
    </div>
    <div style="margin-top:16px">{{ $orders->links() }}</div>
@endif

@endsection
