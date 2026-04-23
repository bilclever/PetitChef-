<?php

namespace App\Http\Controllers\Client;

use App\Events\ReportChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(Request $request): View
    {
        // Pré-remplir depuis une commande si fournie
        $order = null;
        if ($request->query('order_id')) {
            $order = Order::query()
                ->where('client_id', auth()->id())
                ->with('cook:id,name')
                ->find($request->query('order_id'));
        }

        $orders = Order::query()
            ->where('client_id', auth()->id())
            ->with('cook:id,name')
            ->latest()
            ->get(['id', 'cook_id', 'total_price', 'created_at']);

        return view('client.report-create', [
            'order'  => $order,
            'orders' => $orders,
            'types'  => [
                'plat_non_conforme'   => 'Plat non conforme',
                'retard_livraison'    => 'Retard de livraison',
                'comportement'        => 'Comportement inapproprié',
                'qualite'             => 'Problème de qualité',
                'autre'               => 'Autre',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'order_id'    => ['required', 'exists:orders,id'],
            'type'        => ['required', 'in:plat_non_conforme,retard_livraison,comportement,qualite,autre'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('client_id', auth()->id())
            ->with('cook:id,name')
            ->first();

        if (! $order) {
            return back()->withErrors(['order_id' => 'Commande invalide.'])->withInput();
        }

        if (! $order->cook_id) {
            return back()->withErrors(['order_id' => 'Impossible de déterminer le cuisinier pour cette commande.'])->withInput();
        }

        $reportId = DB::table('reports')->insertGetId([
            'client_id'   => auth()->id(),
            'cook_id'     => $order->cook_id,
            'order_id'    => $order->id,
            'dish_id'     => null,
            'type'        => $validated['type'],
            'description' => $validated['description'],
            'status'      => 'open',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        event(new ReportChanged([
            'id' => (int) $reportId,
            'client_id' => (int) auth()->id(),
            'cook_id' => (int) $order->cook_id,
            'order_id' => (int) $order->id,
            'type' => (string) $validated['type'],
            'description' => (string) $validated['description'],
            'status' => 'open',
            'admin_note' => null,
            'client_name' => auth()->user()?->name,
            'cook_name' => $order->cook?->name,
            'dish_name' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], 'created'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Signalement envoyé. L\'administrateur va examiner votre demande.',
                'report_id' => (int) $reportId,
            ]);
        }

        return redirect()->route('client.orders.history')
            ->with('status', '✅ Signalement envoyé. L\'administrateur va examiner votre demande.');
    }

    public function index(): View
    {
        $reports = DB::table('reports')
            ->where('reports.client_id', auth()->id())
            ->leftJoin('users as cooks', 'cooks.id', '=', 'reports.cook_id')
            ->leftJoin('orders', 'orders.id', '=', 'reports.order_id')
            ->select([
                'reports.id',
                'reports.type',
                'reports.description',
                'reports.status',
                'reports.admin_note',
                'reports.created_at',
                'cooks.name as cook_name',
                'orders.id as order_ref',
            ])
            ->orderByDesc('reports.created_at')
            ->paginate(10);

        return view('client.reports', [
            'reports' => $reports,
            'typeLabels' => [
                'plat_non_conforme' => 'Plat non conforme',
                'retard_livraison'  => 'Retard de livraison',
                'comportement'      => 'Comportement inapproprié',
                'qualite'           => 'Problème de qualité',
                'autre'             => 'Autre',
            ],
            'statusLabels' => [
                'open'      => 'Ouvert',
                'in_review' => 'En traitement',
                'resolved'  => 'Résolu',
                'rejected'  => 'Rejeté',
            ],
            'statusColors' => [
                'open'      => ['bg' => '#FEF0EA', 'color' => '#C2623F'],
                'in_review' => ['bg' => '#EAF0FE', 'color' => '#3B6FD4'],
                'resolved'  => ['bg' => '#EFF5F0', 'color' => '#6B8C6E'],
                'rejected'  => ['bg' => '#FFE4E1', 'color' => '#c0392b'],
            ],
        ]);
    }
}
