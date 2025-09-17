<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Solicitud;
use App\Models\Gerencia;
use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Mostrar vista de reportes con filtros y resultados paginados.
     */
    public function index(Request $request)
    {
        // filtros aceptados
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status'); // pendiente, aprobado, rechazado
        $fromGer = $request->query('from_gerencia_id');
        $toGer = $request->query('to_gerencia_id');
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page', 15);

        $query = Solicitud::with(['product', 'fromGerencia', 'toGerencia', 'creator']);

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
        if ($toGer) {
            $query->where('to_gerencia_id', $toGer);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $solicitudes = $query->latest()->paginate($perPage)->withQueryString();

        // totales para el resumen
        $totales = [
            'total' => Solicitud::count(),
            'pendiente' => Solicitud::where('status', 'pendiente')->count(),
            'aprobado' => Solicitud::where('status', 'aprobado')->count(),
            'rechazado' => Solicitud::where('status', 'rechazado')->count(),
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
                'to_gerencia_id' => $toGer,
                'product_id' => $productId,
                'per_page' => $perPage,
            ],
            'gerencias' => $gerencias,
            'products' => $products,
        ]);
    }

    /**
     * Exportar resultados filtrados a CSV (misma lógica de filtros que index).
     */
    public function exportCsv(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');
        $fromGer = $request->query('from_gerencia_id');
        $toGer = $request->query('to_gerencia_id');
        $productId = $request->query('product_id');

        $query = Solicitud::with(['product', 'fromGerencia', 'toGerencia', 'creator']);

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($status) $query->where('status', $status);
        if ($fromGer) $query->where('from_gerencia_id', $fromGer);
        if ($toGer) $query->where('to_gerencia_id', $toGer);
        if ($productId) $query->where('product_id', $productId);

        $fileName = 'report_solicitudes_' . now()->format('Ymd_His') . '.csv';

        /** StreamedResponse para no agotar memoria */
        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w+');
            // Cabeceras CSV
            fputcsv($handle, [
                'ID', 'Código', 'Producto', 'From Gerencia', 'To Gerencia',
                'Cantidad', 'Estado', 'Descripción', 'Creado Por', 'Fecha Creación'
            ]);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->code,
                        optional($row->product)->name,
                        optional($row->fromGerencia)->name,
                        optional($row->toGerencia)->name,
                        $row->quantity ?? '',
                        $row->status,
                        $row->description,
                        optional($row->creator)->name,
                        $row->created_at ? $row->created_at->toDateTimeString() : '',
                    ]);
                }
            });

            fclose($handle);
        });

        $disposition = "attachment; filename={$fileName}";
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
