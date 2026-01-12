<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Solicitud;
use App\Models\Gerencia;
use App\Models\Product;
use App\Models\Inventory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

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

    public function inventory(Request $request)
    {
        $search = $request->query('search');
        $from = $request->query('from');
        $to = $request->query('to');
        $tipoMaterial = $request->query('tipo_material');
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page', 15);

        $query = Inventory::with('product')
            ->when($search, function ($q, $value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('location', 'like', '%' . $value . '%')
                        ->orWhere('notes', 'like', '%' . $value . '%')
                        ->orWhereHas('product', function ($productQuery) use ($value) {
                            $productQuery->where('name', 'like', '%' . $value . '%')
                                ->orWhere('code', 'like', '%' . $value . '%');
                        });
                });
            })
            ->when($productId, fn ($q, $value) => $q->where('product_id', $value))
            ->when($from, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($to, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        $tipoMaterialValues = collect(Arr::wrap($tipoMaterial))
            ->filter(fn ($item) => in_array($item, ['equipos', 'herramientas', 'consumibles'], true))
            ->values();

        if ($tipoMaterialValues->isNotEmpty()) {
            $query->whereHas('product', fn ($q) => $q->whereIn('tipo_material', $tipoMaterialValues));
        }

        $totals = (clone $query)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity_total')
            ->selectRaw('COALESCE(SUM(reserved_quantity), 0) as reserved_total')
            ->selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0) as available_total')
            ->first();

        $inventories = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Report/Inventario', [
            'inventories' => $inventories,
            'totals' => [
                'total' => (int) ($totals->total ?? 0),
                'quantity_total' => (int) ($totals->quantity_total ?? 0),
                'reserved_total' => (int) ($totals->reserved_total ?? 0),
                'available_total' => (int) ($totals->available_total ?? 0),
            ],
            'filters' => [
                'search' => $search,
                'from' => $from,
                'to' => $to,
                'tipo_material' => $tipoMaterial,
                'product_id' => $productId,
                'per_page' => $perPage,
            ],
            'products' => Product::select('id', 'name')->get(),
            'tipoMaterialOptions' => [
                ['value' => 'equipos', 'label' => 'Equipos'],
                ['value' => 'herramientas', 'label' => 'Herramientas'],
                ['value' => 'consumibles', 'label' => 'Consumibles'],
            ],
        ]);
    }

    public function inventoryPdf(Request $request)
    {
        $search = $request->query('search');
        $from = $request->query('from');
        $to = $request->query('to');
        $tipoMaterial = $request->query('tipo_material');
        $productId = $request->query('product_id');

        $query = Inventory::with('product')
            ->when($search, function ($q, $value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('location', 'like', '%' . $value . '%')
                        ->orWhere('notes', 'like', '%' . $value . '%')
                        ->orWhereHas('product', function ($productQuery) use ($value) {
                            $productQuery->where('name', 'like', '%' . $value . '%')
                                ->orWhere('code', 'like', '%' . $value . '%');
                        });
                });
            })
            ->when($productId, fn ($q, $value) => $q->where('product_id', $value))
            ->when($from, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($to, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        $tipoMaterialValues = collect(Arr::wrap($tipoMaterial))
            ->filter(fn ($item) => in_array($item, ['equipos', 'herramientas', 'consumibles'], true))
            ->values();

        if ($tipoMaterialValues->isNotEmpty()) {
            $query->whereHas('product', fn ($q) => $q->whereIn('tipo_material', $tipoMaterialValues));
        }

        $inventories = $query->latest()->get();

        $totals = [
            'total' => $inventories->count(),
            'quantity_total' => $inventories->sum('quantity'),
            'reserved_total' => $inventories->sum('reserved_quantity'),
            'available_total' => $inventories->sum(fn ($inv) => $inv->quantity - $inv->reserved_quantity),
        ];

        $pdf = Pdf::loadView('reportes.inventario_pdf', [
            'inventories' => $inventories,
            'totals' => $totals,
            'filters' => [
                'search' => $search,
                'from' => $from,
                'to' => $to,
                'tipo_material' => $tipoMaterial,
                'product_id' => $productId,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte_inventario_' . now()->format('Ymd_His') . '.pdf');
    }

    public function products(Request $request)
    {
        $search = $request->query('search');
        $from = $request->query('from');
        $to = $request->query('to');
        $tipoMaterial = $request->query('tipo_material');
        $perPage = $request->query('per_page', 15);

        $query = Product::query()
            ->when($search, function ($q, $value) {
                $q->where(function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%')
                        ->orWhere('code', 'like', '%' . $value . '%');
                });
            })
            ->when($from, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($to, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        $tipoMaterialValues = collect(Arr::wrap($tipoMaterial))
            ->filter(fn ($item) => in_array($item, ['equipos', 'herramientas', 'consumibles'], true))
            ->values();

        if ($tipoMaterialValues->isNotEmpty()) {
            $query->whereIn('tipo_material', $tipoMaterialValues);
        }

        $totals = [
            'total' => (clone $query)->count(),
            'equipos' => (clone $query)->where('tipo_material', 'equipos')->count(),
            'herramientas' => (clone $query)->where('tipo_material', 'herramientas')->count(),
            'consumibles' => (clone $query)->where('tipo_material', 'consumibles')->count(),
        ];

        $products = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Report/Productos', [
            'products' => $products,
            'totals' => $totals,
            'filters' => [
                'search' => $search,
                'from' => $from,
                'to' => $to,
                'tipo_material' => $tipoMaterial,
                'per_page' => $perPage,
            ],
            'tipoMaterialOptions' => [
                ['value' => 'equipos', 'label' => 'Equipos'],
                ['value' => 'herramientas', 'label' => 'Herramientas'],
                ['value' => 'consumibles', 'label' => 'Consumibles'],
            ],
        ]);
    }

    public function productsPdf(Request $request)
    {
        $search = $request->query('search');
        $from = $request->query('from');
        $to = $request->query('to');
        $tipoMaterial = $request->query('tipo_material');

        $query = Product::query()
            ->when($search, function ($q, $value) {
                $q->where(function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%')
                        ->orWhere('code', 'like', '%' . $value . '%');
                });
            })
            ->when($from, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($to, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        $tipoMaterialValues = collect(Arr::wrap($tipoMaterial))
            ->filter(fn ($item) => in_array($item, ['equipos', 'herramientas', 'consumibles'], true))
            ->values();

        if ($tipoMaterialValues->isNotEmpty()) {
            $query->whereIn('tipo_material', $tipoMaterialValues);
        }

        $products = $query->latest()->get();

        $totals = [
            'total' => $products->count(),
            'equipos' => $products->where('tipo_material', 'equipos')->count(),
            'herramientas' => $products->where('tipo_material', 'herramientas')->count(),
            'consumibles' => $products->where('tipo_material', 'consumibles')->count(),
        ];

        $pdf = Pdf::loadView('reportes.productos_pdf', [
            'products' => $products,
            'totals' => $totals,
            'filters' => [
                'search' => $search,
                'from' => $from,
                'to' => $to,
                'tipo_material' => $tipoMaterial,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte_productos_' . now()->format('Ymd_His') . '.pdf');
    }
}
