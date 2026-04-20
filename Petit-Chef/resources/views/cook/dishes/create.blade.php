@extends('layouts.app')

@section('content')
<div style="max-width:600px;margin:0 auto">
    <div style="margin-bottom:24px">
        <a href="{{ route('cook.dashboard') }}" style="font-size:13px;color:var(--mid-gray);text-decoration:none;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
        </a>
        <h1 class="pc-title" style="margin-top:8px">Publier un <em style="font-style:italic;color:var(--terracotta)">plat</em></h1>
    </div>

    <div class="pc-card" style="padding:28px">
        <form method="POST" action="{{ route('cook.dishes.store') }}" enctype="multipart/form-data">
            @csrf
            @include('cook.dishes._form')
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <a href="{{ route('cook.dashboard') }}" class="pc-btn">Annuler</a>
                <button type="submit" class="pc-btn pc-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                    Publier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
