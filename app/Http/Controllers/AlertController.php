<?php

namespace App\Http\Controllers;

use App\Services\AlertSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertController extends Controller
{
    public function index(Request $request, AlertSummary $summary)
    {
        if ($request->user()) {
            $request->user()->forceFill(['alerts_seen_at' => now()])->save();
        }

        $pendingQuery = $summary->pendingApprovalsQuery($request->user());
        $pendingApprovals = collect();

        if ($pendingQuery) {
            $pendingApprovals = $pendingQuery
                ->with(['product', 'fromGerencia', 'creator'])
                ->latest('updated_at')
                ->get();
        }

        return Inertia::render('Alerts/Index', [
            'pendingApprovals' => $pendingApprovals,
            'canApprove' => (bool) $pendingQuery,
        ]);
    }

    public function summary(Request $request, AlertSummary $summary)
    {
        return response()->json(
            $summary->forUser($request->user())
        );
    }
}
