<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'PetitChef') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,500;0,700;1,300&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @stack('styles')
        <style>
            :root {
                --cream: #F9F5EE;
                --warm-white: #FDFAF5;
                --terracotta: #C2623F;
                --terracotta-dark: #A04E30;
                --sage: #6B8C6E;
                --charcoal: #2C2C2A;
                --mid-gray: #7A7A76;
                --light-gray: #E8E4DC;
                --border: #DDD8CE;
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                font-family: 'DM Sans', sans-serif;
                background: var(--cream);
                color: var(--charcoal);
                font-size: 14px;
                line-height: 1.6;
            }

            .pc-shell { min-height: 100vh; position: relative; }
            .pc-bg {
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(circle at top left, rgba(194, 98, 63, 0.20), transparent 35%),
                    radial-gradient(circle at bottom right, rgba(107, 140, 110, 0.14), transparent 30%);
                pointer-events: none;
            }

            .pc-nav {
                position: sticky;
                top: 0;
                z-index: 20;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 24px;
                background: rgba(253, 250, 245, 0.85);
                border-bottom: 1px solid var(--border);
                backdrop-filter: blur(8px);
            }

            .pc-brand {
                font-family: 'Fraunces', serif;
                font-size: 24px;
                font-weight: 700;
                color: var(--terracotta);
                text-decoration: none;
                letter-spacing: -0.4px;
            }

            .pc-brand span { color: var(--charcoal); font-style: italic; font-weight: 300; }

            .pc-nav-links { display: flex; gap: 10px; align-items: center; }

            .pc-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border-radius: 10px;
                border: 1.5px solid var(--border);
                background: transparent;
                color: var(--charcoal);
                padding: 9px 14px;
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all .18s;
            }

            .pc-btn:hover { border-color: var(--terracotta); color: var(--terracotta); }

            .pc-btn-primary {
                border-color: var(--terracotta);
                background: var(--terracotta);
                color: #fff;
            }

            .pc-btn-primary:hover {
                background: var(--terracotta-dark);
                border-color: var(--terracotta-dark);
                color: #fff;
            }

            .pc-main {
                position: relative;
                z-index: 2;
                max-width: 1120px;
                margin: 0 auto;
                padding: 28px 20px 36px;
            }

            .pc-alert {
                border-radius: 12px;
                padding: 12px 14px;
                margin-bottom: 16px;
                border: 1px solid;
                font-size: 13px;
            }

            .pc-alert-success {
                color: #1f5f3a;
                border-color: #9fd1ab;
                background: #eaf6ee;
            }

            .pc-alert-error {
                color: #9a2f26;
                border-color: #e6b2ac;
                background: #fdf1ef;
            }

            .pc-card {
                background: var(--warm-white);
                border: 1px solid var(--border);
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(44, 44, 42, 0.07);
            }

            .pc-title {
                font-family: 'Fraunces', serif;
                font-size: 30px;
                font-weight: 500;
                line-height: 1.15;
                letter-spacing: -0.4px;
                margin: 0;
            }

            .pc-subtitle { margin: 6px 0 0; color: var(--mid-gray); font-size: 13px; }

            .pc-field { display: flex; flex-direction: column; gap: 6px; }
            .pc-label { font-size: 12px; font-weight: 500; color: var(--mid-gray); }
            .pc-input, .pc-select, .pc-textarea {
                width: 100%;
                border: 1.5px solid var(--border);
                border-radius: 10px;
                background: var(--warm-white);
                color: var(--charcoal);
                padding: 10px 12px;
                font-size: 13px;
                font-family: 'DM Sans', sans-serif;
                outline: none;
                transition: border-color .18s;
            }

            .pc-input:focus, .pc-select:focus, .pc-textarea:focus { border-color: var(--terracotta); }
            .pc-textarea { min-height: 90px; resize: vertical; }

            .pc-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 3px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
            }

            .pc-status::before {
                content: '';
                width: 6px;
                height: 6px;
                border-radius: 50%;
                display: block;
            }

            .pc-status-pending { background: #fef7e8; color: #c47a20; }
            .pc-status-pending::before { background: #d4973a; }
            .pc-status-approved { background: #eff5f0; color: var(--sage); }
            .pc-status-approved::before { background: var(--sage); }
            .pc-status-rejected { background: #fdeadf; color: #c0392b; }
            .pc-status-rejected::before { background: #c0392b; }

            .pc-table-wrap {
                overflow-x: auto;
                border-radius: 12px;
                border: 1px solid var(--border);
                background: var(--warm-white);
            }

            .pc-table { width: 100%; border-collapse: collapse; }
            .pc-table th {
                text-align: left;
                padding: 11px 14px;
                font-size: 11px;
                letter-spacing: .6px;
                text-transform: uppercase;
                color: var(--mid-gray);
                background: var(--cream);
                border-bottom: 1px solid var(--border);
            }
            .pc-table td {
                padding: 12px 14px;
                border-bottom: 1px solid var(--border);
                font-size: 13px;
            }
            .pc-table tr:last-child td { border-bottom: none; }

            @media (max-width: 760px) {
                .pc-nav { padding: 10px 14px; }
                .pc-brand { font-size: 20px; }
                .pc-main { padding: 18px 12px 24px; }
                .pc-title { font-size: 26px; }
            }
        </style>
    </head>
    <body>
        <div class="pc-shell">
            <div class="pc-bg"></div>
            <header class="pc-nav">
                <div style="display:flex;align-items:center;gap:24px">
                    <a href="{{ route('dashboard') }}" class="pc-brand">petit<span>Chef</span></a>
                    @auth
                    <nav style="display:flex;gap:4px">
                        <a href="{{ route('menu') }}" class="pc-btn {{ request()->routeIs('menu') ? 'pc-btn-primary' : '' }}" style="padding:7px 14px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Menu du jour
                        </a>
                        @if(auth()->user()->role === 'cook')
                        <a href="{{ route('cook.dashboard') }}" class="pc-btn {{ request()->routeIs('cook.*') ? 'pc-btn-primary' : '' }}" style="padding:7px 14px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                            Espace Cuisinier
                        </a>
                        @endif
                    </nav>
                    @endauth
                </div>
                <nav class="pc-nav-links">
                    @auth
                    <a href="{{ route('profile.edit') }}" class="pc-btn">Mon profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="pc-btn pc-btn-primary" type="submit">Se déconnecter</button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="pc-btn">Connexion</a>
                    <a href="{{ route('register') }}" class="pc-btn pc-btn-primary">Inscription</a>
                    @endauth
                </nav>
            </header>

            <main class="pc-main">
                @if (session('status'))
                    <div class="pc-alert pc-alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="pc-alert pc-alert-error">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>