<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\SolicitudStatusLog;
use App\Models\Product;
use App\Models\Gerencia;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    protected array $statusOptions = [
        'pendiente' => 'Normalización',
        'vp_mantenimiento' => 'VP. Mantenimiento',
        'vp_soporte' => 'VP. Soporte',
        'administracion' => 'Administración',
        'compras' => 'Compras',
        'normalizacion_final' => 'Normalización (Entrega)',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
    ];

    protected array $stageGerencias = [
        'pendiente' => 'Normalización',
        'vp_mantenimiento' => 'VP. Mantenimiento',
        'vp_soporte' => 'VP. Soporte',
        'administracion' => 'Administración',
        'compras' => 'Compras',
        'normalizacion_final' => 'Normalización',
    ];

    // Listado de solicitudes
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pendiente,vp_mantenimiento,vp_soporte,administracion,compras,normalizacion_final,aprobado,rechazado',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $filters = array_merge([
            'search' => null,
            'status' => null,
            'from_date' => null,
            'to_date' => null,
        ], $filters);

        $user = Auth::user();
        $workflowRole = $this->resolveWorkflowRole($user);

        $solicitudes = Solicitud::with([
                'product',
                'fromGerencia',
                'toGerencia',
                'creator',
                'statusLogs.user',
                'latestStatusLog',
            ])
            ->when($filters['search'], function ($query, $value) {
                $query->where(function ($subQuery) use ($value) {
                    $subQuery->where('code', 'like', '%' . $value . '%')
                        ->orWhereHas('product', function ($productQuery) use ($value) {
                            $productQuery->where('name', 'like', '%' . $value . '%');
                        });
                });
            })
            ->when($filters['status'], fn ($query, $value) => $query->where('status', $value))
            ->when($filters['from_date'], fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['to_date'], fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->when($workflowRole === 'viewer', function ($query) use ($user) {
                $query->where(function ($sub) use ($user) {
                    $sub->where('created_by', $user->id);
                    if ($user->gerencia_id) {
                        $sub->orWhere('from_gerencia_id', $user->gerencia_id)
                            ->orWhere('to_gerencia_id', $user->gerencia_id);
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Solicitudes/Index', [
            'solicitudes' => $solicitudes,
            'filters' => $filters,
            'workflow' => [
                'role' => $workflowRole,
                'status_options' => $this->statusOptions,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Solicitudes/Create', [
            'products' => Product::all(),
            'inventories' => Inventory::all(),
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'request_type.required' => 'El tipo de solicitud es obligatorio.',
            'request_type.in' => 'El tipo de solicitud seleccionado no es válido.',
            'product_id.required' => 'Debes seleccionar un producto.',
            'product_id.exists' => 'El producto seleccionado no es válido.',
            'inventory_id.required_if' => 'Debes seleccionar un inventario cuando la solicitud es de inventario.',
            'inventory_id.exists' => 'El inventario seleccionado no es válido.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
            'description.string' => 'La descripción debe ser un texto válido.',
            'return_date.date' => 'La fecha de devolución no tiene un formato válido.',
        ];

        $attributes = [
            'product_id' => 'producto',
            'inventory_id' => 'inventario',
            'quantity' => 'cantidad',
            'return_date' => 'fecha de devolución',
        ];

        $data = $request->validate([
            'request_type' => 'required|in:inventario,compra',
            'product_id' => 'required|exists:products,id',
            'inventory_id' => 'nullable|exists:inventories,id|required_if:request_type,inventario',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'return_date' => 'nullable|date',
        ], $messages, $attributes);

        $user = Auth::user();
        $product = Product::findOrFail($data['product_id']);
        $inventory = $data['request_type'] === 'inventario'
            ? Inventory::find($data['inventory_id'])
            : null;

        if ($inventory && $inventory->product_id !== $product->id) {
            return back()->withErrors(['inventory_id' => 'El inventario seleccionado no pertenece al producto elegido']);
        }

        if ($inventory && $data['quantity'] > ($inventory->quantity - $inventory->reserved_quantity)) {
            return back()->withErrors(['quantity' => 'La cantidad solicitada supera el stock disponible']);
        }

        if ($inventory && in_array($product->tipo_material, ['equipos', 'herramientas'], true) && empty($data['return_date'])) {
            return back()->withErrors(['return_date' => 'Debe indicar una fecha de devolución para equipos y herramientas.']);
        }

        $normalizacionId = $this->getGerenciaIdByName('Normalización');
        if (!$normalizacionId) {
            return back()->withErrors(['general' => 'No se encontró la gerencia de Normalización.']);
        }

        $returnDate = ($inventory && in_array($product->tipo_material, ['equipos', 'herramientas'], true))
            ? ($data['return_date'] ?? null)
            : null;

        DB::beginTransaction();
        try {
            $solicitud = Solicitud::create([
                'code' => strtoupper(uniqid('REQ-')),
                'product_id' => $product->id,
                'inventory_id' => $inventory?->id,
                'quantity' => (int) $data['quantity'],
                'approved_quantity' => 0,
                'created_by' => $user->id,
                'from_gerencia_id' => $user->gerencia_id ?? null,
                'to_gerencia_id' => $normalizacionId,
                'status' => 'pendiente',
                'description' => $data['description'] ?? null,
                'return_date' => $returnDate,
            ]);

            if ($inventory) {
                $this->reserveInventory($inventory, (int) $data['quantity']);
            }

            $this->createStatusLog(
                $solicitud,
                'created',
                'pendiente',
                $data['description'] ?? 'Solicitud creada'
            );

            DB::commit();

            return redirect()->route('solicitudes.index')->with('success', 'Solicitud creada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Ocurrió un error al crear la solicitud']);
        }
    }

    public function updateStatus(Request $request, Solicitud $solicitud)
    {
        $messages = [
            'action.required' => 'Debes seleccionar una acción.',
            'action.in' => 'La acción seleccionada no es válida.',
            'justification.required' => 'La justificación es obligatoria.',
            'justification.string' => 'La justificación debe ser un texto válido.',
            'justification.min' => 'La justificación debe tener al menos :min caracteres.',
            'justification.max' => 'La justificación no puede superar :max caracteres.',
            'priority.required' => 'Debes seleccionar la prioridad.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'attachment.file' => 'El documento adjunto debe ser un archivo válido.',
            'attachment.max' => 'El documento adjunto no puede superar los 5 MB.',
            'attachment.mimes' => 'El documento adjunto debe ser un archivo de tipo PDF o imagen.',
            'approved_quantity.integer' => 'La cantidad aprobada debe ser un número entero.',
            'approved_quantity.min' => 'La cantidad aprobada debe ser al menos 1.',
        ];

        $role = $this->resolveWorkflowRole(Auth::user());
        $action = $request->input('action');
        $priorityRule = ($action === 'approve_stage' && $role === 'normalizacion')
            ? 'required|in:alto,medio,bajo'
            : 'nullable|in:alto,medio,bajo';

        $data = $request->validate([
            'action' => 'required|in:approve_stage,reject_stage,compras_approve,compras_reject,finalize_order',
            'justification' => 'required|string|min:3|max:1000',
            'priority' => $priorityRule,
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'approved_quantity' => 'nullable|integer|min:1',
        ], $messages);
        $inventory = $solicitud->inventory_id ? Inventory::find($solicitud->inventory_id) : null;

        if ($solicitud->inventory_id && !$inventory) {
            return back()->withErrors(['inventory' => 'Inventario no encontrado']);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('solicitudes/' . $solicitud->id, 'public');
        }

        DB::beginTransaction();
        try {
            switch ($data['action']) {
                case 'approve_stage':
                    $isInventoryRequest = $this->isInventoryRequest($solicitud);
                    $allowedStages = $isInventoryRequest
                        ? ['pendiente', 'vp_mantenimiento']
                        : ['pendiente', 'vp_mantenimiento', 'vp_soporte', 'administracion'];

                    if (!in_array($solicitud->status, $allowedStages, true)) {
                        throw ValidationException::withMessages([
                            'action' => 'La acción seleccionada no es válida para el estatus actual.',
                        ]);
                    }

                    $this->ensureRoleForStage($solicitud->status, $role);

                    $nextStage = $this->getNextStage($solicitud, $solicitud->status);

                    if (!$nextStage) {
                        if (!$isInventoryRequest) {
                            throw ValidationException::withMessages([
                                'action' => 'No se pudo determinar el siguiente paso del flujo.',
                            ]);
                        }

                        $remaining = $solicitud->quantity - $solicitud->approved_quantity;
                        if ($inventory && $remaining > 0) {
                            $this->consumeInventory($inventory, $remaining);
                        }

                        $solicitud->approved_quantity = $solicitud->quantity;
                        $solicitud->status = 'aprobado';
                        $solicitud->to_gerencia_id = null;
                        $solicitud->save();

                        $this->createStatusLog(
                            $solicitud,
                            'inventory_finalized',
                            'aprobado',
                            $data['justification'],
                            $attachmentPath
                        );
                        break;
                    }

                    if ($role === 'normalizacion') {
                        $solicitud->priority = $data['priority'];
                    }
                    $solicitud->status = $nextStage;
                    $solicitud->to_gerencia_id = $this->getGerenciaIdForStage($nextStage);
                    $solicitud->save();

                    $this->createStatusLog(
                        $solicitud,
                        'approve_stage',
                        $nextStage,
                        $data['justification'],
                        $attachmentPath
                    );
                    break;

                case 'reject_stage':
                    $isInventoryRequest = $this->isInventoryRequest($solicitud);
                    $allowedStages = $isInventoryRequest
                        ? ['pendiente', 'vp_mantenimiento']
                        : ['pendiente', 'vp_mantenimiento', 'vp_soporte', 'administracion', 'normalizacion_final'];

                    if (!in_array($solicitud->status, $allowedStages, true)) {
                        throw ValidationException::withMessages([
                            'action' => 'La acción seleccionada no es válida para el estatus actual.',
                        ]);
                    }

                    $this->ensureRoleForStage($solicitud->status, $role);

                    if ($solicitud->status === 'pendiente') {
                        if ($inventory) {
                            $remaining = $solicitud->quantity - $solicitud->approved_quantity;
                            if ($remaining > 0) {
                                $this->releaseReservation($inventory, $remaining);
                            }
                        }
                        $solicitud->status = 'rechazado';
                        $solicitud->to_gerencia_id = null;
                    } elseif ($solicitud->status === 'normalizacion_final') {
                        $solicitud->status = 'compras';
                        $solicitud->to_gerencia_id = $this->getGerenciaIdForStage('compras');
                    } else {
                        $previousStage = $this->getPreviousStage($solicitud, $solicitud->status) ?? 'pendiente';
                        $solicitud->status = $previousStage;
                        $solicitud->to_gerencia_id = $this->getGerenciaIdForStage($previousStage);
                    }

                    $solicitud->save();

                    $this->createStatusLog(
                        $solicitud,
                        'reject_stage',
                        $solicitud->status,
                        $data['justification'],
                        $attachmentPath
                    );
                    break;

                case 'compras_approve':
                    if (!in_array($solicitud->status, ['compras', 'normalizacion_final'], true)) {
                        throw ValidationException::withMessages([
                            'action' => 'La acción seleccionada no es válida para el estatus actual.',
                        ]);
                    }

                    $this->ensureRoleForStage('compras', $role);

                    $approvedNow = (int) ($data['approved_quantity'] ?? 0);
                    if ($approvedNow <= 0) {
                        throw ValidationException::withMessages([
                            'approved_quantity' => 'Debes indicar la cantidad aprobada.',
                        ]);
                    }

                    $remaining = $solicitud->quantity - $solicitud->approved_quantity;
                    if ($remaining <= 0) {
                        throw ValidationException::withMessages([
                            'approved_quantity' => 'Ya se aprobó la cantidad total solicitada.',
                        ]);
                    }

                    if ($approvedNow > $remaining) {
                        throw ValidationException::withMessages([
                            'approved_quantity' => 'La cantidad aprobada excede la cantidad pendiente.',
                        ]);
                    }

                    if ($inventory) {
                        $this->consumeInventory($inventory, $approvedNow);
                    }

                    $solicitud->approved_quantity += $approvedNow;
                    $solicitud->status = 'normalizacion_final';
                    $solicitud->to_gerencia_id = $this->getGerenciaIdForStage('normalizacion_final');
                    $solicitud->save();

                    $this->createStatusLog(
                        $solicitud,
                        'compras_approve',
                        'normalizacion_final',
                        $data['justification'],
                        $attachmentPath,
                        $approvedNow
                    );
                    break;

                case 'compras_reject':
                    if (!in_array($solicitud->status, ['compras', 'normalizacion_final'], true)) {
                        throw ValidationException::withMessages([
                            'action' => 'La acción seleccionada no es válida para el estatus actual.',
                        ]);
                    }

                    $this->ensureRoleForStage('compras', $role);

                    $solicitud->status = 'administracion';
                    $solicitud->to_gerencia_id = $this->getGerenciaIdForStage('administracion');
                    $solicitud->save();

                    $this->createStatusLog(
                        $solicitud,
                        'compras_reject',
                        'administracion',
                        $data['justification'],
                        $attachmentPath
                    );
                    break;

                case 'finalize_order':
                    if ($solicitud->status !== 'normalizacion_final') {
                        throw ValidationException::withMessages([
                            'action' => 'La solicitud debe estar en Normalización para finalizar la orden.',
                        ]);
                    }

                    $this->ensureRoleForStage('normalizacion_final', $role);

                    if ($solicitud->approved_quantity < $solicitud->quantity) {
                        throw ValidationException::withMessages([
                            'action' => 'Aún quedan cantidades pendientes por aprobar.',
                        ]);
                    }

                    $solicitud->status = 'aprobado';
                    $solicitud->to_gerencia_id = null;
                    $solicitud->save();

                    $this->createStatusLog(
                        $solicitud,
                        'finalize_order',
                        'aprobado',
                        $data['justification'],
                        $attachmentPath
                    );
                    break;
            }

            DB::commit();

            return back()->with('success', 'Solicitud actualizada correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            return back()->withErrors(['general' => 'Ocurrió un error al actualizar la solicitud']);
        }
    }

    public function generatePDF($id)
    {
        $solicitud = Solicitud::with([
            'product',
            'fromGerencia',
            'toGerencia',
            'creator',
            'statusLogs.user.gerencia',
        ])->findOrFail($id);
        $isApproved = $solicitud->status === 'aprobado';
        $view = $isApproved ? 'solicitudes.orden_entrega_pdf' : 'solicitudes.pdf';
        $filename = $isApproved
            ? "nota_entrega_{$solicitud->code}.pdf"
            : "minuta_{$solicitud->code}.pdf";

        $pdf = Pdf::loadView($view, compact('solicitud'));
        return $pdf->stream($filename);
    }

    public function viewAttachment(Request $request, Solicitud $solicitud, SolicitudStatusLog $log)
    {
        if ($log->solicitud_id !== $solicitud->id) {
            abort(404);
        }

        $user = $request->user();
        $isAdmin = $user && $user->hasAnyRole(['super_admin', 'admin']);

        if (!$isAdmin) {
            $allowed = $user && (
                $solicitud->created_by === $user->id
                || ($user->gerencia_id && (
                    $solicitud->from_gerencia_id === $user->gerencia_id
                    || $solicitud->to_gerencia_id === $user->gerencia_id
                ))
            );

            if (!$allowed) {
                abort(403);
            }
        }

        if (!$log->attachment_path || !Storage::disk('public')->exists($log->attachment_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($log->attachment_path));
    }

    protected function resolveWorkflowRole($user): string
    {
        if ($user->hasRole('super_admin')) {
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

    protected function getGerenciaIdByName(string $name): ?int
    {
        static $cache = [];

        if (array_key_exists($name, $cache)) {
            return $cache[$name];
        }

        $cache[$name] = Gerencia::where('name', $name)->value('id');

        return $cache[$name];
    }

    protected function isInventoryRequest(Solicitud $solicitud): bool
    {
        return !is_null($solicitud->inventory_id);
    }

    protected function stageSequenceFor(Solicitud $solicitud): array
    {
        return $this->isInventoryRequest($solicitud)
            ? ['pendiente', 'vp_mantenimiento']
            : ['pendiente', 'vp_mantenimiento', 'vp_soporte', 'administracion', 'compras', 'normalizacion_final'];
    }

    protected function getNextStage(Solicitud $solicitud, string $currentStage): ?string
    {
        $sequence = $this->stageSequenceFor($solicitud);
        $index = array_search($currentStage, $sequence, true);
        if ($index === false) {
            return null;
        }

        return $sequence[$index + 1] ?? null;
    }

    protected function getPreviousStage(Solicitud $solicitud, string $currentStage): ?string
    {
        $sequence = $this->stageSequenceFor($solicitud);
        $index = array_search($currentStage, $sequence, true);
        if ($index === false || $index === 0) {
            return null;
        }

        return $sequence[$index - 1];
    }

    protected function getGerenciaIdForStage(string $stage): ?int
    {
        $name = $this->stageGerencias[$stage] ?? null;
        return $name ? $this->getGerenciaIdByName($name) : null;
    }

    protected function ensureRoleForStage(string $stage, string $role): void
    {
        if ($role === 'super_admin') {
            return;
        }

        $expectedRole = match ($stage) {
            'pendiente', 'normalizacion_final' => 'normalizacion',
            'vp_mantenimiento' => 'vp_mantenimiento',
            'vp_soporte' => 'vp_soporte',
            'administracion' => 'administracion',
            'compras' => 'compras',
            default => null,
        };

        if ($expectedRole === null || $expectedRole !== $role) {
            throw ValidationException::withMessages([
                'action' => 'No tienes permisos para realizar esta acción.',
            ]);
        }
    }

    protected function reserveInventory(Inventory $inventory, int $amount): void
    {
        $inventory->reserved_quantity += $amount;
        $inventory->save();
    }

    protected function releaseReservation(Inventory $inventory, int $amount): void
    {
        $inventory->reserved_quantity = max(0, $inventory->reserved_quantity - $amount);
        $inventory->save();
    }

    protected function consumeInventory(Inventory $inventory, int $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'approved_quantity' => 'La cantidad debe ser mayor a 0.',
            ]);
        }

        if ($inventory->reserved_quantity < $amount) {
            throw ValidationException::withMessages([
                'approved_quantity' => 'La cantidad reservada es inferior a la solicitada.',
            ]);
        }

        if ($inventory->quantity < $amount) {
            throw ValidationException::withMessages([
                'approved_quantity' => 'El inventario disponible es insuficiente.',
            ]);
        }

        $inventory->quantity = max(0, $inventory->quantity - $amount);
        $inventory->reserved_quantity = max(0, $inventory->reserved_quantity - $amount);
        $inventory->save();
    }

    protected function createStatusLog(
        Solicitud $solicitud,
        string $action,
        string $status,
        ?string $justification = null,
        ?string $attachmentPath = null,
        ?int $approvedQuantity = null
    ): void {
        SolicitudStatusLog::create([
            'solicitud_id' => $solicitud->id,
            'status' => $status,
            'action' => $action,
            'justification' => $justification,
            'attachment_path' => $attachmentPath,
            'approved_quantity' => $approvedQuantity,
            'changed_by' => Auth::id(),
        ]);
    }
}
