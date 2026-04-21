<?php

namespace App\Http\Controllers;

use App\Events\ReportChanged;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'status' => (string) $request->query('status', ''),
            'served_date' => (string) $request->query('served_date', now()->toDateString()),
            'cook_id' => (string) $request->query('cook_id', ''),
            'report_status' => (string) $request->query('report_status', ''),
            'user_status' => (string) $request->query('user_status', ''),
        ];

        $orders = $this->buildOrdersPaginator($filters);
        $stats = $this->computeStats($filters['served_date']);
        $reports = Schema::hasTable('reports')
            ? $this->buildReportsQuery($filters)->paginate(10, ['*'], 'reports_page')->withQueryString()
            : new LengthAwarePaginator([], 0, 10, null, ['pageName' => 'reports_page']);

        $users = Schema::hasColumn('users', 'account_status')
            ? $this->buildUsersQuery($filters)->paginate(10, ['*'], 'users_page')->withQueryString()
            : new LengthAwarePaginator([], 0, 10, null, ['pageName' => 'users_page']);

        return view('admin.dashboard', [
            'orders' => $orders,
            'reports' => $reports,
            'users' => $users,
            'filters' => $filters,
            'cooks' => User::query()->where('role', 'cook')->orderBy('name')->get(['id', 'name']),
            'pendingCooks' => User::query()->where('role', 'cook')->where('approval_status', 'pending')->orderByDesc('created_at')->get(),
            'stats' => $stats,
            'statusLabels' => [
                'recue' => 'Reçue',
                'en_preparation' => 'En préparation',
                'prete' => 'Prête',
                'livree' => 'Livrée',
                'annulee' => 'Annulée',
            ],
            'reportLabels' => [
                'open' => 'Ouvert',
                'in_review' => 'En traitement',
                'resolved' => 'Résolu',
                'rejected' => 'Rejeté',
            ],
            'accountStatusLabels' => [
                'active' => 'Actif',
                'suspended' => 'Suspendu',
                'banned' => 'Banni',
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $servedDate = (string) $request->query('served_date', now()->toDateString());

        return response()->json($this->computeStats($servedDate));
    }

    public function updateCookStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role !== 'cook', 422, 'Utilisateur invalide pour cette action.');

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'comment' => ['required_if:decision,reject', 'nullable', 'string', 'max:1000'],
        ]);

        $decision = (string) $validated['decision'];
        $comment = trim((string) ($validated['comment'] ?? ''));

        $user->approval_status = $decision === 'approve' ? 'approved' : 'rejected';

        if (Schema::hasColumn('users', 'is_verified')) {
            $user->setAttribute('is_verified', $decision === 'approve');
        }

        if ($decision === 'approve') {
            $user->rejection_reason = null;
        } else {
            $user->rejection_reason = $comment !== '' ? $comment : 'Profil refusé par l’admin.';
        }

        $user->save();

        return back()->with('status', $decision === 'approve'
            ? 'Cuisinier validé avec succès.'
            : 'Cuisinier rejeté.');
    }

    public function updateReportStatus(Request $request, int $reportId): RedirectResponse
    {
        if (! Schema::hasTable('reports')) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_review,resolved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNote = trim((string) ($validated['admin_note'] ?? '')) ?: null;

        DB::table('reports')->where('id', $reportId)->update([
            'status' => $validated['status'],
            'admin_note' => $adminNote,
            'updated_at' => now(),
        ]);

        $report = DB::table('reports')
            ->leftJoin('users as clients', 'clients.id', '=', 'reports.client_id')
            ->leftJoin('users as cooks', 'cooks.id', '=', 'reports.cook_id')
            ->leftJoin('dishes', 'dishes.id', '=', 'reports.dish_id')
            ->select([
                'reports.id',
                'reports.client_id',
                'reports.cook_id',
                'reports.order_id',
                'reports.type',
                'reports.description',
                'reports.status',
                'reports.admin_note',
                'reports.created_at',
                'reports.updated_at',
                'clients.name as client_name',
                'cooks.name as cook_name',
                'dishes.name as dish_name',
            ])
            ->where('id', $reportId)
            ->first();

        if ($report) {
            event(new ReportChanged([
                'id' => (int) $report->id,
                'client_id' => (int) $report->client_id,
                'cook_id' => (int) ($report->cook_id ?? 0),
                'order_id' => $report->order_id,
                'type' => (string) $report->type,
                'description' => (string) $report->description,
                'status' => (string) $report->status,
                'admin_note' => $report->admin_note,
                'client_name' => $report->client_name,
                'cook_name' => $report->cook_name,
                'dish_name' => $report->dish_name,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ], 'updated'));
        }

        return back()->with('status', 'Signalement mis à jour.');
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 422, 'Impossible de modifier un administrateur.');

        $validated = $request->validate([
            'account_status' => ['required', 'in:active,suspended,banned'],
            'account_status_reason' => ['required_if:account_status,suspended,banned', 'nullable', 'string', 'max:1000'],
        ]);

        if (! Schema::hasColumn('users', 'account_status')) {
            abort(500, 'Colonne account_status manquante.');
        }

        $user->account_status = $validated['account_status'];
        $user->account_status_reason = trim((string) ($validated['account_status_reason'] ?? '')) ?: null;
        $user->save();

        return back()->with('status', 'Statut utilisateur mis à jour.');
    }

    protected function buildOrdersPaginator(array $filters): LengthAwarePaginator
    {
        if (! Schema::hasTable('orders')) {
            return new LengthAwarePaginator([], 0, 12);
        }

        $query = DB::table('orders')
            ->leftJoin('users as clients', 'clients.id', '=', 'orders.client_id')
            ->leftJoin('users as cooks', 'cooks.id', '=', 'orders.cook_id')
            ->select([
                'orders.id',
                'orders.status',
                'orders.total_price',
                'orders.pickup_time',
                'orders.created_at',
                'orders.cook_id',
                'clients.name as client_name',
                'cooks.name as cook_name',
            ]);

        if ($filters['status'] !== '') {
            $query->where('orders.status', $filters['status']);
        }

        if ($filters['served_date'] !== '') {
            $query->whereDate('orders.created_at', $filters['served_date']);
        }

        if ($filters['cook_id'] !== '') {
            $query->where('orders.cook_id', (int) $filters['cook_id']);
        }

        return $query
            ->orderByDesc('orders.created_at')
            ->paginate(12, ['*'], 'orders_page')
            ->withQueryString();
    }

    protected function buildReportsQuery(array $filters)
    {
        $query = DB::table('reports')
            ->leftJoin('users as clients', 'clients.id', '=', 'reports.client_id')
            ->leftJoin('users as cooks', 'cooks.id', '=', 'reports.cook_id')
            ->select([
                'reports.id',
                'reports.type',
                'reports.description',
                'reports.status',
                'reports.admin_note',
                'reports.created_at',
                'clients.name as client_name',
                'cooks.name as cook_name',
            ]);

        if (Schema::hasTable('orders')) {
            $query->leftJoin('orders', 'orders.id', '=', 'reports.order_id')
                ->addSelect('orders.id as order_ref');
        } else {
            $query->addSelect(DB::raw('NULL as order_ref'));
        }

        if (Schema::hasTable('dishes')) {
            $query->leftJoin('dishes', 'dishes.id', '=', 'reports.dish_id')
                ->addSelect('dishes.name as dish_name');
        } else {
            $query->addSelect(DB::raw('NULL as dish_name'));
        }

        if ($filters['report_status'] !== '') {
            $query->where('reports.status', $filters['report_status']);
        }

        return $query->orderByDesc('reports.created_at');
    }

    protected function buildUsersQuery(array $filters)
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'role', 'account_status', 'account_status_reason', 'approval_status', 'phone', 'created_at'])
            ->where('role', '!=', 'admin')
            ->orderByDesc('created_at');

        if ($filters['user_status'] !== '' && Schema::hasColumn('users', 'account_status')) {
            $query->where('account_status', $filters['user_status']);
        }

        return $query;
    }

    protected function computeStats(string $servedDate): array
    {
        if (! Schema::hasTable('orders')) {
            return [
                'ca_by_cook' => collect(),
                'cancellation_rate' => 0,
                'top_dishes' => collect(),
                'user_stats' => collect(),
            ];
        }

        $ordersBase = DB::table('orders')->whereDate('created_at', $servedDate);

        $caByCook = (clone $ordersBase)
            ->when($this->hasPaidColumns(), fn (Builder $query): Builder => $this->applyPaidFilter($query))
            ->select('cook_id', DB::raw('SUM(total_price) as revenue'))
            ->groupBy('cook_id')
            ->orderByDesc('revenue')
            ->get();

        $total = (clone $ordersBase)->count();

        $cancelled = (clone $ordersBase)
            ->whereIn('status', ['annulee', 'annulée', 'cancelled'])
            ->count();

        $cancellationRate = $total > 0 ? round(($cancelled / $total) * 100, 2) : 0;

        $topDishes = collect();

        if (Schema::hasTable('order_dish')) {
            if (Schema::hasTable('dishes')) {
                $topDishes = DB::table('order_dish')
                    ->join('orders', 'orders.id', '=', 'order_dish.order_id')
                    ->leftJoin('dishes', 'dishes.id', '=', 'order_dish.dish_id')
                    ->whereDate('orders.created_at', $servedDate)
                    ->select([
                        'order_dish.dish_id',
                        'dishes.name as dish_name',
                        DB::raw('SUM(order_dish.quantity) as sold_qty'),
                    ])
                    ->groupBy('order_dish.dish_id', 'dishes.name')
                    ->orderByDesc('sold_qty')
                    ->limit(5)
                    ->get();
            } else {
                $topDishes = DB::table('order_dish')
                    ->join('orders', 'orders.id', '=', 'order_dish.order_id')
                    ->whereDate('orders.created_at', $servedDate)
                    ->select([
                        'order_dish.dish_id',
                        DB::raw("CONCAT('Plat #', order_dish.dish_id) as dish_name"),
                        DB::raw('SUM(order_dish.quantity) as sold_qty'),
                    ])
                    ->groupBy('order_dish.dish_id')
                    ->orderByDesc('sold_qty')
                    ->limit(5)
                    ->get();
            }
        }

        $cookIds = $caByCook->pluck('cook_id')->filter()->all();
        $cookNames = empty($cookIds)
            ? collect()
            : User::query()->whereIn('id', $cookIds)->pluck('name', 'id');

        $caByCook = $caByCook->map(function (object $item) use ($cookNames): object {
            $item->cook_name = $cookNames[$item->cook_id] ?? 'Cuisinier #'.$item->cook_id;

            return $item;
        });

        $usersBase = User::query()
            ->where('role', '!=', 'admin')
            ->get(['id', 'name', 'role']);

        $userStats = $usersBase->mapWithKeys(function (User $user): array {
            return [
                (int) $user->id => (object) [
                    'user_id' => (int) $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'client_orders' => 0,
                    'client_total_spent' => 0.0,
                    'client_cancelled_orders' => 0,
                    'cook_orders' => 0,
                    'cook_revenue' => 0.0,
                    'reports_count' => 0,
                ],
            ];
        });

        $clientStats = DB::table('orders')
            ->whereDate('created_at', $servedDate)
            ->select([
                'client_id',
                DB::raw('COUNT(*) as client_orders'),
                DB::raw('SUM(total_price) as client_total_spent'),
                DB::raw("SUM(CASE WHEN status IN ('annulee','annulée','cancelled') THEN 1 ELSE 0 END) as client_cancelled_orders"),
            ])
            ->groupBy('client_id')
            ->get();

        foreach ($clientStats as $row) {
            $userId = (int) $row->client_id;

            if (! isset($userStats[$userId])) {
                continue;
            }

            $userStats[$userId]->client_orders = (int) $row->client_orders;
            $userStats[$userId]->client_total_spent = (float) $row->client_total_spent;
            $userStats[$userId]->client_cancelled_orders = (int) $row->client_cancelled_orders;
        }

        $cookStats = DB::table('orders')
            ->whereDate('created_at', $servedDate)
            ->select([
                'cook_id',
                DB::raw('COUNT(*) as cook_orders'),
                DB::raw('SUM(total_price) as cook_revenue'),
            ])
            ->groupBy('cook_id')
            ->get();

        foreach ($cookStats as $row) {
            $userId = (int) $row->cook_id;

            if (! isset($userStats[$userId])) {
                continue;
            }

            $userStats[$userId]->cook_orders = (int) $row->cook_orders;
            $userStats[$userId]->cook_revenue = (float) $row->cook_revenue;
        }

        if (Schema::hasTable('reports')) {
            $reportClientStats = DB::table('reports')
                ->whereDate('created_at', $servedDate)
                ->select('client_id', DB::raw('COUNT(*) as reports_count'))
                ->groupBy('client_id')
                ->get();

            foreach ($reportClientStats as $row) {
                $userId = (int) $row->client_id;

                if (! isset($userStats[$userId])) {
                    continue;
                }

                $userStats[$userId]->reports_count += (int) $row->reports_count;
            }

            $reportCookStats = DB::table('reports')
                ->whereDate('created_at', $servedDate)
                ->whereNotNull('cook_id')
                ->select('cook_id', DB::raw('COUNT(*) as reports_count'))
                ->groupBy('cook_id')
                ->get();

            foreach ($reportCookStats as $row) {
                $userId = (int) $row->cook_id;

                if (! isset($userStats[$userId])) {
                    continue;
                }

                $userStats[$userId]->reports_count += (int) $row->reports_count;
            }
        }

        $userStats = $userStats
            ->values()
            ->sortByDesc(function (object $item): int {
                return $item->client_orders + $item->cook_orders + $item->reports_count;
            });

        return [
            'ca_by_cook' => $caByCook,
            'cancellation_rate' => $cancellationRate,
            'top_dishes' => $topDishes,
            'user_stats' => $userStats,
        ];
    }

    protected function hasPaidColumns(): bool
    {
        return Schema::hasColumn('orders', 'is_paid') || Schema::hasColumn('orders', 'payment_status');
    }

    protected function applyPaidFilter(Builder $query): Builder
    {
        if (Schema::hasColumn('orders', 'is_paid')) {
            return $query->where('is_paid', true);
        }

        if (Schema::hasColumn('orders', 'payment_status')) {
            return $query->whereIn('payment_status', ['paid', 'succeeded']);
        }

        return $query;
    }
}
