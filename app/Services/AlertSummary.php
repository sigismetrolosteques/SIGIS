<?php

namespace App\Services;

use App\Models\Solicitud;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AlertSummary
{
    public function forUser(?User $user): array
    {
        $query = $this->pendingApprovalsQuery($user);

        if (!$user || !$query) {
            return $this->emptySummary(false);
        }

        $pendingCount = (clone $query)->count();
        $latest = (clone $query)->max('updated_at');
        $latestAt = $latest ? Carbon::parse($latest) : null;

        $seenAt = $user->alerts_seen_at ? Carbon::parse($user->alerts_seen_at) : null;
        $hasUnseen = $pendingCount > 0 && (!$seenAt || !$latestAt || $latestAt->greaterThan($seenAt));

        return [
            'total' => $pendingCount,
            'pendingApprovals' => $pendingCount,
            'latestAt' => $latestAt?->toIso8601String(),
            'unseen' => $hasUnseen,
            'canApprove' => true,
        ];
    }

    public function pendingApprovalsQuery(?User $user): ?Builder
    {
        if (!$user) {
            return null;
        }

        $role = $this->resolveWorkflowRole($user);
        $query = Solicitud::query();

        switch ($role) {
            case 'super_admin':
                $query->where(function ($sub) {
                    $sub->whereIn('status', [
                        'pendiente',
                        'vp_mantenimiento',
                        'vp_soporte',
                        'administracion',
                        'normalizacion_final',
                    ])->orWhere(function ($inner) {
                        $inner->where('status', 'compras')
                            ->whereColumn('quantity', '>', 'approved_quantity');
                    });
                });
                break;
            case 'normalizacion':
                $query->where(function ($sub) {
                    $sub->where('status', 'pendiente')
                        ->orWhere(function ($inner) {
                            $inner->where('status', 'normalizacion_final')
                                ->whereColumn('quantity', '<=', 'approved_quantity');
                        });
                });
                break;
            case 'vp_mantenimiento':
                $query->where('status', 'vp_mantenimiento');
                break;
            case 'vp_soporte':
                $query->where('status', 'vp_soporte');
                break;
            case 'administracion':
                $query->where('status', 'administracion');
                break;
            case 'compras':
                $query->whereIn('status', ['compras', 'normalizacion_final'])
                    ->whereColumn('quantity', '>', 'approved_quantity');
                break;
            default:
                return null;
        }

        return $query;
    }

    protected function resolveWorkflowRole(User $user): string
    {
        if ($user->hasAnyRole(['super_admin', 'super admin'])) {
            return 'super_admin';
        }

        if (!$user->hasRole('admin')) {
            return 'viewer';
        }

        return match (optional($user->gerencia)->name) {
            'Normalización' => 'normalizacion',
            'VP. Mantenimiento' => 'vp_mantenimiento',
            'VP. Soporte' => 'vp_soporte',
            'Administración' => 'administracion',
            'Compras' => 'compras',
            default => 'viewer',
        };
    }

    protected function emptySummary(bool $canApprove): array
    {
        return [
            'total' => 0,
            'pendingApprovals' => 0,
            'latestAt' => null,
            'unseen' => false,
            'canApprove' => $canApprove,
        ];
    }
}
