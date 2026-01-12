<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Audit;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $action = $request->query('action');
        $model = $request->query('model');
        $userId = $request->query('user_id');
        $from = $request->query('from');
        $to = $request->query('to');
        $perPage = (int) $request->query('per_page', 20);

        $query = Audit::with(['user', 'auditable']);

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhereJsonContains('new_values', $q)
                    ->orWhereJsonContains('old_values', $q);
            });
        }
        if ($action) {
            $query->where('action', $action);
        }
        if ($model) {
            // esperar class name parcial o FQCN: aceptamos tanto 'Solicitud' como 'App\Models\Solicitud'
            if (!str_contains($model, '\\')) {
                // convertir Solicitud -> App\Models\Solicitud
                $modelClass = "App\\Models\\{$model}";
            } else {
                $modelClass = $model;
            }
            $query->where('auditable_type', $modelClass);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $audits = $query->latest()->paginate($perPage)->withQueryString();
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Audit/Index', [
            'audits' => $audits,
            'users' => $users,
            'filters' => [
                'q' => $q,
                'action' => $action,
                'model' => $model,
                'user_id' => $userId,
                'from' => $from,
                'to' => $to,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = Audit::with(['user', 'auditable']);

        // aplicar mismos filtros que index (puedes extraer a método si quieres)
        if ($request->query('q')) {
            $q = $request->query('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhereJsonContains('new_values', $q)
                    ->orWhereJsonContains('old_values', $q);
            });
        }
        if ($request->query('action')) {
            $query->where('action', $request->query('action'));
        }
        if ($request->query('model')) {
            $m = $request->query('model');
            $m = str_contains($m, '\\') ? $m : "App\\Models\\{$m}";
            $query->where('auditable_type', $m);
        }
        if ($request->query('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }
        if ($request->query('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->query('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        $fileName = 'audits_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w+');
            // BOM para Excel y encabezado
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID','Usuario','Modelo','Modelo ID','Acción','Valores antiguos','Valores nuevos','IP','User Agent','Fecha'], ';');

            $query->orderBy('created_at', 'desc')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        optional($row->user)->name,
                        $row->auditable_type,
                        $row->auditable_id,
                        $row->action,
                        $row->old_values ? json_encode($row->old_values, JSON_UNESCAPED_UNICODE) : '',
                        $row->new_values ? json_encode($row->new_values, JSON_UNESCAPED_UNICODE) : '',
                        $row->ip_address,
                        $row->user_agent,
                        $row->created_at ? $row->created_at->toDateTimeString() : '',
                    ], ';');
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");

        return $response;
    }
}
