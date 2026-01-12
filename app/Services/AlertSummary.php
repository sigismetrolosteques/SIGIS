<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Solicitud;
use Carbon\Carbon;
use App\Models\User;

class AlertSummary
{
    public function forUser(?User $user, int $threshold = 5): array
    {
        if (!$user) {
            return [
                'total' => 0,
                'lowStock' => 0,
                'overdueReturns' => 0,
                'threshold' => $threshold,
                'latestAt' => null,
                'unseen' => false,
            ];
        }

        $lowStockQuery = Inventory::whereRaw('(quantity - reserved_quantity) <= ?', [$threshold]);
        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockLatest = (clone $lowStockQuery)->max('updated_at');
        $lowStockLatestAt = $lowStockLatest ? Carbon::parse($lowStockLatest) : null;

        $overdueQuery = Solicitud::query()
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', now()->toDateString())
            ->where('status', 'aprobado');

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);

        if (!$isAdmin) {
            $overdueQuery->where(function ($sub) use ($user) {
                $sub->where('created_by', $user->id);
                if ($user->gerencia_id) {
                    $sub->orWhere('from_gerencia_id', $user->gerencia_id)
                        ->orWhere('to_gerencia_id', $user->gerencia_id);
                }
            });
        }

        $overdueCount = (clone $overdueQuery)->count();
        $overdueReturnLatest = (clone $overdueQuery)->max('return_date');
        $overdueUpdatedLatest = (clone $overdueQuery)->max('updated_at');
        $overdueReturnAt = $overdueReturnLatest
            ? Carbon::parse($overdueReturnLatest)->addDay()->startOfDay()
            : null;
        $overdueUpdatedAt = $overdueUpdatedLatest ? Carbon::parse($overdueUpdatedLatest) : null;
        $overdueLatestAt = null;

        foreach ([$overdueReturnAt, $overdueUpdatedAt] as $candidate) {
            if ($candidate && (!$overdueLatestAt || $candidate->greaterThan($overdueLatestAt))) {
                $overdueLatestAt = $candidate;
            }
        }

        $latestAt = $lowStockLatestAt;
        if ($overdueLatestAt && (!$latestAt || $overdueLatestAt->greaterThan($latestAt))) {
            $latestAt = $overdueLatestAt;
        }

        $total = $lowStockCount + $overdueCount;
        $seenAt = $user->alerts_seen_at ? Carbon::parse($user->alerts_seen_at) : null;
        $hasUnseen = $total > 0 && (!$seenAt || !$latestAt || $latestAt->greaterThan($seenAt));

        return [
            'total' => $total,
            'lowStock' => $lowStockCount,
            'overdueReturns' => $overdueCount,
            'threshold' => $threshold,
            'latestAt' => $latestAt?->toIso8601String(),
            'unseen' => $hasUnseen,
        ];
    }
}
