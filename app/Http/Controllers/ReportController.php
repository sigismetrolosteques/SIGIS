<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Solicitud;
use App\Models\Gerencia;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // filtros aceptados
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status'); // pendiente, aprobado, rechazado
        $fromGer = $request->query('from_gerencia_id');
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page', 15);

        $user = Auth::user();
        $isAdmin = $user?->hasAnyRole(['super_admin', 'admin']);

        $baseQuery = Solicitud::query();

        if ($user && !$isAdmin) {
            $baseQuery->where('created_by', $user->id);
        }

        $query = (clone $baseQuery)->with(['product', 'fromGerencia', 'toGerencia', 'creator']);

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($fromGer) {
            $query->where('from_gerencia_id', $fromGer);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $solicitudes = $query->latest()->paginate($perPage)->withQueryString();

        // totales para el resumen
        $totales = [
            'total' => (clone $baseQuery)->count(),
            'en_proceso' => (clone $baseQuery)->whereNotIn('status', ['aprobado', 'rechazado'])->count(),
            'aprobado' => (clone $baseQuery)->where('status', 'aprobado')->count(),
            'rechazado' => (clone $baseQuery)->where('status', 'rechazado')->count(),
        ];

        $gerencias = Gerencia::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        return Inertia::render('Report/Solicitudes', [
            'solicitudes' => $solicitudes,
            'totales' => $totales,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'from_gerencia_id' => $fromGer,
                'product_id' => $productId,
                'per_page' => $perPage,
            ],
            'gerencias' => $gerencias,
            'products' => $products,
        ]);
    }

    public function exportPDF(Request $request)
    {
        // Meter la misma lógica de filtros
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');
        $fromGer = $request->query('from_gerencia_id');
        $productId = $request->query('product_id');

        $user = Auth::user();
        $isAdmin = $user?->hasAnyRole(['super_admin', 'admin']);

        $baseQuery = Solicitud::query();

        if ($user && !$isAdmin) {
            $baseQuery->where('created_by', $user->id);
        }

        $query = (clone $baseQuery)->with(['product', 'fromGerencia', 'toGerencia', 'creator']);

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($status) $query->where('status', $status);
        if ($fromGer) $query->where('from_gerencia_id', $fromGer);
        if ($productId) $query->where('product_id', $productId);

        $solicitudes = $query->latest()->get();

        $totales = [
            'total' => $solicitudes->count(),
            'en_proceso' => $solicitudes->whereNotIn('status', ['aprobado', 'rechazado'])->count(),
            'aprobado' => $solicitudes->where('status', 'aprobado')->count(),
            'rechazado' => $solicitudes->where('status', 'rechazado')->count(),
        ];

        $pdf = Pdf::loadView('reportes.solicitudes_pdf', [
            'solicitudes' => $solicitudes,
            'totales' => $totales,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'from_gerencia_id' => $fromGer,
                'product_id' => $productId,
            ],
        ])->setPaper('a4', 'landscape'); // por ejemplo

        return $pdf->download('reporte_solicitudes_'.now()->format('Ymd_His').'.pdf');
    }
}
