@extends('layouts.app')

@section('content')
<section class="pc-card" style="padding:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 class="pc-title">Dashboard administrateur</h1>
            <p class="pc-subtitle">Supervision des commandes, validation des profils, signalements, utilisateurs et statistiques globales.</p>
        </div>
        <span class="pc-status pc-status-approved">Jour: {{ $filters['served_date'] }}</span>
    </div>
</section>

<section style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-top:14px;">
    <div class="pc-card" style="padding:14px;">
        <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Taux d'annulation</div>
        <div style="font-family:'Fraunces',serif;font-size:28px;margin-top:6px;">{{ $stats['cancellation_rate'] }}%</div>
    </div>
    <div class="pc-card" style="padding:14px;">
        <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Top 5 plats</div>
        <div style="margin-top:8px;font-size:13px;display:grid;gap:4px;">
            @forelse ($stats['top_dishes'] as $dish)
                <div>{{ $dish->dish_name }} · {{ $dish->sold_qty }}</div>
            @empty
                <div style="color:var(--mid-gray);">Aucune vente enregistrée.</div>
            @endforelse
        </div>
    </div>
    <div class="pc-card" style="padding:14px;">
        <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">CA par cuisinier</div>
        <div style="margin-top:8px;font-size:13px;display:grid;gap:4px;">
            @forelse ($stats['ca_by_cook'] as $row)
                <div>{{ $row->cook_name }} · {{ number_format((float) $row->revenue, 0, ',', ' ') }} FCFA</div>
            @empty
                <div style="color:var(--mid-gray);">Aucun revenu sur la période.</div>
            @endforelse
        </div>
    </div>
    <div class="pc-card" style="padding:14px;">
        <div style="font-size:11px;color:var(--mid-gray);text-transform:uppercase;letter-spacing:.6px;">Utilisateurs actifs</div>
        <div style="font-family:'Fraunces',serif;font-size:28px;margin-top:6px;">
            {{ collect($stats['user_stats'])->filter(fn ($u) => (($u->client_orders + $u->cook_orders) > 0))->count() }}
        </div>
        <div style="font-size:12px;color:var(--mid-gray);margin-top:2px;">Avec activité sur la journée</div>
    </div>
</section>

<section class="pc-card" style="padding:14px;margin-top:14px;">
    <form method="GET" action="{{ route('admin.dashboard') }}" style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;align-items:end;">
        <label class="pc-field">
            <span class="pc-label">Statut</span>
            <select name="status" class="pc-select">
                <option value="">Tous</option>
                @foreach ($statusLabels as $statusKey => $statusLabel)
                    <option value="{{ $statusKey }}" @selected($filters['status'] === $statusKey)>{{ $statusLabel }}</option>
                @endforeach
            </select>
        </label>
        <label class="pc-field">
            <span class="pc-label">Jour</span>
            <input class="pc-input" type="date" name="served_date" value="{{ $filters['served_date'] }}">
        </label>
        <label class="pc-field">
            <span class="pc-label">Cuisinier</span>
            <select name="cook_id" class="pc-select">
                <option value="">Tous</option>
                @foreach ($cooks as $cook)
                    <option value="{{ $cook->id }}" @selected($filters['cook_id'] === (string) $cook->id)>{{ $cook->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="pc-field">
            <span class="pc-label">Signalement</span>
            <select name="report_status" class="pc-select">
                <option value="">Tous</option>
                @foreach ($reportLabels as $reportKey => $reportLabel)
                    <option value="{{ $reportKey }}" @selected($filters['report_status'] === $reportKey)>{{ $reportLabel }}</option>
                @endforeach
            </select>
        </label>
        <label class="pc-field">
            <span class="pc-label">Utilisateurs</span>
            <select name="user_status" class="pc-select">
                <option value="">Tous</option>
                @foreach ($accountStatusLabels as $accountKey => $accountLabel)
                    <option value="{{ $accountKey }}" @selected($filters['user_status'] === $accountKey)>{{ $accountLabel }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="pc-btn pc-btn-primary">Filtrer</button>
    </form>
</section>

<section style="display:grid;grid-template-columns:1.3fr .7fr;gap:14px;margin-top:14px;">
    <div class="pc-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Liste des commandes</h2>
        </div>

        @if ($orders->total() === 0)
            <div style="padding:16px;color:var(--mid-gray);">Aucune commande pour les filtres sélectionnés.</div>
        @else
            <div class="pc-table-wrap" style="border:none;border-radius:0;">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Cuisinier</th>
                            <th>Total</th>
                            <th>Récupération</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr data-order-row="{{ $order->id }}">
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->client_name ?? '-' }}</td>
                                <td>{{ $order->cook_name ?? '-' }}</td>
                                <td>{{ number_format((float) $order->total_price, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $order->pickup_time ? \Carbon\Carbon::parse($order->pickup_time)->format('d/m H:i') : '—' }}</td>
                                <td><span class="pc-status pc-status-pending" data-order-status="{{ $order->id }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:12px 14px;">{{ $orders->links() }}</div>
        @endif
    </div>

    <aside class="pc-card" style="padding:16px;">
        <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Validation cuisiniers</h2>
        <p class="pc-subtitle" style="margin-top:4px;">Profils en attente de validation.</p>

        <div style="display:grid;gap:10px;margin-top:12px;">
            @forelse ($pendingCooks as $cook)
                <div class="pc-card" style="padding:10px;border-radius:12px;">
                    <div style="font-weight:600;">{{ $cook->name }}</div>
                    <div style="font-size:12px;color:var(--mid-gray);">{{ $cook->email }}</div>
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <form method="POST" action="{{ route('admin.cooks.status', $cook) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="decision" value="approve">
                            <button class="pc-btn pc-btn-primary" type="submit" style="padding:6px 10px;">Valider</button>
                        </form>
                        <form method="POST" action="{{ route('admin.cooks.status', $cook) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="decision" value="reject">
                            <button class="pc-btn" type="submit" style="padding:6px 10px;">Rejeter</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="color:var(--mid-gray);font-size:13px;">Aucun profil en attente.</div>
            @endforelse
        </div>
    </aside>
</section>

<section style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
    <div class="pc-card" style="padding:16px;">
        <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Validation profils cuisiniers</h2>
        <p class="pc-subtitle">Valider ou rejeter un profil avec commentaire.</p>

        <div style="display:grid;gap:10px;margin-top:12px;">
            @forelse ($pendingCooks as $cook)
                <form class="pc-card" method="POST" action="{{ route('admin.cooks.status', $cook) }}" style="padding:12px;border-radius:12px;display:grid;gap:10px;">
                    @csrf
                    @method('PATCH')
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                        <div>
                            <div style="font-weight:600;">{{ $cook->name }}</div>
                            <div style="font-size:12px;color:var(--mid-gray);">{{ $cook->email }} · {{ $cook->phone }}</div>
                        </div>
                        <span class="pc-status pc-status-pending">En attente</span>
                    </div>
                    <textarea name="comment" class="pc-textarea" placeholder="Commentaire obligatoire si rejet..."></textarea>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="pc-btn pc-btn-primary" type="submit" name="decision" value="approve">Valider</button>
                        <button class="pc-btn" type="submit" name="decision" value="reject" style="border-color:#e6b2ac;color:#c0392b">Rejeter</button>
                    </div>
                </form>
            @empty
                <div style="color:var(--mid-gray);font-size:13px;">Aucun profil en attente.</div>
            @endforelse
        </div>
    </div>

    <div class="pc-card" style="padding:16px;">
        <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Signalements clients</h2>
        <p class="pc-subtitle">Plats non conformes, litiges et suivi admin.</p>

        <div id="admin-reports-list" style="display:grid;gap:10px;margin-top:12px;">
            @forelse ($reports as $report)
                <form class="pc-card" data-report-card="{{ $report->id }}" method="POST" action="{{ route('admin.reports.status', $report->id) }}" style="padding:12px;border-radius:12px;display:grid;gap:10px;">
                    @csrf
                    @method('PATCH')
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                        <div>
                            <div style="font-weight:600;" data-report-type="{{ $report->id }}">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</div>
                            <div style="font-size:12px;color:var(--mid-gray);" data-report-meta="{{ $report->id }}">Client: {{ $report->client_name ?? '-' }} · Cuisinier: {{ $report->cook_name ?? '-' }}</div>
                            <div style="font-size:12px;color:var(--mid-gray);" data-report-meta2="{{ $report->id }}">Commande: #{{ $report->order_ref ?? '-' }} · Plat: {{ $report->dish_name ?? '-' }}</div>
                        </div>
                        <span class="pc-status pc-status-pending" data-report-status="{{ $report->id }}">{{ $reportLabels[$report->status] ?? $report->status }}</span>
                    </div>
                    <div style="font-size:13px;line-height:1.5;" data-report-description="{{ $report->id }}">{{ $report->description }}</div>
                    <textarea name="admin_note" class="pc-textarea" data-report-admin-note-input="{{ $report->id }}" placeholder="Note admin...">{{ $report->admin_note }}</textarea>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <select name="status" class="pc-select" data-report-status-select="{{ $report->id }}" style="max-width:180px;">
                            @foreach ($reportLabels as $reportKey => $reportLabel)
                                <option value="{{ $reportKey }}" @selected($report->status === $reportKey)>{{ $reportLabel }}</option>
                            @endforeach
                        </select>
                        <button class="pc-btn pc-btn-primary" type="submit">Mettre à jour</button>
                    </div>
                </form>
            @empty
                <div id="admin-reports-empty" style="color:var(--mid-gray);font-size:13px;">Aucun signalement à traiter.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="pc-card" style="padding:16px;margin-top:14px;">
    <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Statistiques par utilisateur</h2>
    <p class="pc-subtitle">Vue globale des clients et cuisiniers sur la journée filtrée.</p>

    <div class="pc-table-wrap" style="margin-top:12px;border:none;border-radius:0;">
        <table class="pc-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Cmd client</th>
                    <th>Dépenses client</th>
                    <th>Cmd cuisinier</th>
                    <th>CA cuisinier</th>
                    <th>Signalements</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stats['user_stats'] as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ ucfirst($row->role) }}</td>
                        <td>{{ $row->client_orders }}</td>
                        <td>{{ number_format((float) $row->client_total_spent, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $row->cook_orders }}</td>
                        <td>{{ number_format((float) $row->cook_revenue, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $row->reports_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Aucune statistique utilisateur disponible.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="pc-card" style="padding:16px;margin-top:14px;">
    <h2 style="margin:0;font-family:'Fraunces',serif;font-size:18px;font-weight:500;">Gestion utilisateurs</h2>
    <p class="pc-subtitle">Suspendre ou bannir un compte utilisateur.</p>

    <div class="pc-table-wrap" style="margin-top:12px;border:none;border-radius:0;">
        <table class="pc-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="pc-status pc-status-{{ $user->account_status === 'active' ? 'approved' : 'rejected' }}">
                                {{ $accountStatusLabels[$user->account_status] ?? $user->account_status }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display:grid;gap:8px;max-width:260px;">
                                @csrf
                                @method('PATCH')
                                <select name="account_status" class="pc-select">
                                    @foreach ($accountStatusLabels as $accountKey => $accountLabel)
                                        <option value="{{ $accountKey }}" @selected($user->account_status === $accountKey)>{{ $accountLabel }}</option>
                                    @endforeach
                                </select>
                                <input name="account_status_reason" class="pc-input" placeholder="Raison (optionnelle)" value="{{ $user->account_status_reason }}">
                                <button class="pc-btn pc-btn-primary" type="submit">Enregistrer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding-top:12px;">{{ $users->links() }}</div>
</section>

<style>
    @media (max-width: 980px) {
        section[style*='grid-template-columns:1fr 1fr 1fr'] { grid-template-columns: 1fr !important; }
        section[style*='grid-template-columns:1fr 1fr 1fr 1fr'] { grid-template-columns: 1fr 1fr !important; }
        section[style*='grid-template-columns:repeat(4,minmax(0,1fr))'] { grid-template-columns: 1fr 1fr !important; }
        section[style*='grid-template-columns:1.3fr .7fr'] { grid-template-columns: 1fr !important; }
        section[style*='grid-template-columns:repeat(6,minmax(0,1fr))'] { grid-template-columns: 1fr 1fr !important; }
        section[style*='grid-template-columns:1fr 1fr'] { grid-template-columns: 1fr !important; }
    }

    @media (max-width: 640px) {
        section[style*='grid-template-columns:repeat(4,minmax(0,1fr))'] { grid-template-columns: 1fr !important; }
        section[style*='grid-template-columns:repeat(6,minmax(0,1fr))'] { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
