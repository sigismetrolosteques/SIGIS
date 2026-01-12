<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Solicitud;
use App\Services\AlertSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()) {
            $request->user()->forceFill(['alerts_seen_at' => now()])->save();
        }

        $threshold = (int) $request->query('threshold', 5);

        $lowStock = Inventory::with('product')
            ->whereRaw('(quantity - reserved_quantity) <= ?', [$threshold])
            ->orderByRaw('(quantity - reserved_quantity) asc')
            ->get();

        $user = $request->user();
        $isAdmin = $user?->hasAnyRole(['super_admin', 'admin']);

        $overdueQuery = Solicitud::with(['product', 'fromGerencia', 'creator'])
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', now()->toDateString())
            ->where('status', 'aprobado');

        if ($user && !$isAdmin) {
            $overdueQuery->where(function ($sub) use ($user) {
                $sub->where('created_by', $user->id);
                if ($user->gerencia_id) {
                    $sub->orWhere('from_gerencia_id', $user->gerencia_id)
                        ->orWhere('to_gerencia_id', $user->gerencia_id);
                }
            });
        }

        $overdueReturns = $overdueQuery->orderBy('return_date')->get();

        return Inertia::render('Alerts/Index', [
            'threshold' => $threshold,
            'lowStock' => $lowStock,
            'overdueReturns' => $overdueReturns,
        ]);
    }

    public function summary(Request $request, AlertSummary $summary)
    {
        $threshold = (int) $request->query('threshold', 5);

        return response()->json(
            $summary->forUser($request->user(), $threshold)
        );
    }
}
