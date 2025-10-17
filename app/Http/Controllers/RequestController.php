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

class RequestController extends Controller
{
    protected array $adminGerencias = ['Administración', 'VGO', 'Normalización'];

    protected array $statusOptions = [
        'pendiente' => 'Pendiente',
        'vgo' => 'VGO',
        'administracion' => 'Administración',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
    ];

    // Listado de solicitudes
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pendiente,vgo,administracion,aprobado,rechazado',
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
                'destinationDecisionUser',
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
            ->when($workflowRole === 'vgo', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('status', 'vgo')
                        ->orWhere(function ($inner) {
                            $inner->where('status', 'pendiente')
                                ->where(function ($pending) {
                                    $pending->whereNull('destination_decision')
                                        ->orWhere('destination_decision', 'aprobado');
                                });
                        });
                });
            })
            ->when($workflowRole === 'administracion', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('status', 'administracion')
                        ->orWhere(function ($inner) {
                            $inner->where('status', 'pendiente')
                                ->where(function ($pending) {
                                    $pending->whereNull('destination_decision')
                                        ->orWhere('destination_decision', 'aprobado');
                                });
                        });
                });
            })
            ->when($workflowRole === 'normalizacion', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('status', 'pendiente')
                        ->orWhere(function ($inner) {
                            $inner->whereIn('status', ['vgo', 'administracion'])
                                ->where(function ($pending) {
                                    $pending->whereNull('destination_decision')
                                        ->orWhere('destination_decision', 'aprobado');
                                });
                        });
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

    // Formulario de creación
    public function create()
    {
        $products = Product::all();
        $gerencias = Gerencia::whereIn('name', ['Administración', 'VGO'])->get();
        $inventories = Inventory::all();

        return Inertia::render('Solicitudes/Create', [
            'products' => $products,
            'gerencias' => $gerencias,
            'inventories' => $inventories,
        ]);
    }

    // Guardar nueva solicitud y reservar cantidad
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_type' => 'required|in:inventario,compra',
            'product_id' => 'required|exists:products,id',
            'inventory_id' => 'nullable|exists:inventories,id|required_if:request_type,inventario',
            'quantity' => 'required|integer|min:1',
            'to_gerencia_id' => 'required|exists:gerencias,id',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $inventory = $data['request_type'] === 'inventario'
            ? Inventory::find($data['inventory_id'])
            : null;

        if ($data['request_type'] === 'inventario' && !$inventory) {
            return back()->withErrors(['inventory_id' => 'Inventario no válido']);
        }

        if ($inventory && $inventory->product_id != $data['product_id']) {
            return back()->withErrors(['inventory_id' => 'El inventario seleccionado no pertenece al producto elegido']);
        }

        if ($inventory && $data['quantity'] > ($inventory->quantity - $inventory->reserved_quantity)) {
            return back()->withErrors(['quantity' => 'La cantidad solicitada supera el stock disponible']);
        }

        $destinyGerencia = Gerencia::find($data['to_gerencia_id']);
        if (!$destinyGerencia || !in_array($destinyGerencia->name, ['Administración', 'VGO'], true)) {
            return back()->withErrors(['to_gerencia_id' => 'La solicitud sólo puede asignarse a Administración o VGO']);
        }

        DB::beginTransaction();
        try {
            $solicitud = Solicitud::create([
                'code' => strtoupper(uniqid('REQ-')),
                'product_id' => $data['product_id'],
                'inventory_id' => $inventory?->id,
                'quantity' => $data['quantity'],
                'created_by' => $user->id,
                'from_gerencia_id' => $user->gerencia_id ?? null,
                'to_gerencia_id' => $data['to_gerencia_id'],
                'status' => 'pendiente',
                'description' => $data['description'] ?? null,
            ]);

            if ($inventory) {
                $inventory->reserved_quantity += (int) $data['quantity'];
                $inventory->save();
            }

            $this->createStatusLog($solicitud, 'created', 'pendiente', $solicitud->description);

            DB::commit();
            return redirect()->route('solicitudes.index')->with('success', 'Solicitud creada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Ocurrió un error al crear la solicitud']);
        }
    }

    // Actualizar estado
    public function updateStatus(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'action' => 'required|in:transfer_vgo,transfer_administracion,approve_destination,reject_destination,approve_final,reject_final',
            'justification' => 'required|string|min:3|max:1000',
        ]);

        $user = Auth::user();
        $role = $this->resolveWorkflowRole($user);
        $inventory = $solicitud->inventory_id ? Inventory::find($solicitud->inventory_id) : null;

        if ($solicitud->inventory_id && !$inventory) {
            return back()->withErrors(['inventory' => 'Inventario no encontrado']);
        }

        DB::beginTransaction();
        try {
            switch ($data['action']) {
                case 'transfer_vgo':
                case 'transfer_administracion':
                    $this->assertCanTransfer($role, $solicitud);
                    $targetStatus = $data['action'] === 'transfer_vgo' ? 'vgo' : 'administracion';
                    $solicitud->status = $targetStatus;
                    $solicitud->destination_decision = null;
                    $solicitud->destination_decision_by = null;
                    $solicitud->destination_decision_at = null;
                    $solicitud->destination_justification = null;
                    $destinyId = $this->getGerenciaIdByName($targetStatus === 'vgo' ? 'VGO' : 'Administración');
                    if ($destinyId) {
                        $solicitud->to_gerencia_id = $destinyId;
                    }
                    $solicitud->save();
                    $this->createStatusLog($solicitud, 'transferred', $targetStatus, $data['justification']);
                    break;

                case 'approve_destination':
                    $this->assertCanApproveDestination($role, $solicitud);
                    $solicitud->destination_decision = 'aprobado';
                    $solicitud->destination_decision_by = $user->id;
                    $solicitud->destination_decision_at = now();
                    $solicitud->destination_justification = $data['justification'];
                    $solicitud->save();
                    $this->createStatusLog($solicitud, 'destination_approved', $solicitud->status, $data['justification']);
                    break;

                case 'reject_destination':
                    $this->assertCanApproveDestination($role, $solicitud);
                    if ($inventory) {
                        $this->releaseReservation($inventory, $solicitud);
                    }
                    $solicitud->status = 'rechazado';
                    $solicitud->destination_decision = 'rechazado';
                    $solicitud->destination_decision_by = $user->id;
                    $solicitud->destination_decision_at = now();
                    $solicitud->destination_justification = $data['justification'];
                    $solicitud->save();
                    $this->createStatusLog($solicitud, 'destination_rejected', 'rechazado', $data['justification']);
                    break;

                case 'approve_final':
                    $this->assertCanFinalize($role, $solicitud);
                    if ($solicitud->destination_decision !== 'aprobado') {
                        throw ValidationException::withMessages([
                            'action' => 'La solicitud debe ser aprobada por la gerencia asignada antes de la aprobación final.',
                        ]);
                    }

                    if ($inventory) {
                        $this->consumeInventory($inventory, $solicitud);
                    }
                    $solicitud->status = 'aprobado';
                    $solicitud->save();
                    $this->createStatusLog($solicitud, 'final_approved', 'aprobado', $data['justification']);
                    break;

                case 'reject_final':
                    $this->assertCanFinalize($role, $solicitud);
                    if ($inventory) {
                        $this->releaseReservation($inventory, $solicitud);
                    }
                    $solicitud->status = 'rechazado';
                    $solicitud->destination_decision = 'rechazado';
                    $solicitud->destination_decision_by = $user->id;
                    $solicitud->destination_decision_at = now();
                    $solicitud->destination_justification = $data['justification'];
                    $solicitud->save();
                    $this->createStatusLog($solicitud, 'final_rejected', 'rechazado', $data['justification']);
                    break;
            }

            DB::commit();
            return back()->with('success', 'Solicitud actualizada correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Ocurrió un error al actualizar la solicitud']);
        }
    }

    // Generar PDF
    public function generatePDF($id)
    {
        $solicitud = Solicitud::with(['product', 'fromGerencia', 'toGerencia', 'statusLogs.user'])->findOrFail($id);
        $pdf = Pdf::loadView('solicitudes.pdf', compact('solicitud'));
        return $pdf->stream("minuta_{$solicitud->code}.pdf");
    }

    protected function resolveWorkflowRole($user): string
    {
        if ($user->hasRole('super_admin')) {
            return 'super_admin';
        }

        $gerenciaName = optional($user->gerencia)->name;

        if ($user->hasRole('admin')) {
            if ($gerenciaName === 'Normalización') {
                return 'normalizacion';
            }
            if ($gerenciaName === 'VGO') {
                return 'vgo';
            }
            if ($gerenciaName === 'Administración') {
                return 'administracion';
            }
        }

        return 'viewer';
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

    protected function assertCanTransfer(string $role, Solicitud $solicitud): void
    {
        if (!in_array($role, ['super_admin', 'normalizacion'], true) || $solicitud->status !== 'pendiente') {
            throw ValidationException::withMessages([
                'action' => 'No tienes permisos para transferir esta solicitud.',
            ]);
        }
    }

    protected function assertCanApproveDestination(string $role, Solicitud $solicitud): void
    {
        $allowedStatuses = ['vgo', 'administracion'];

        if (!in_array($solicitud->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'action' => 'La solicitud debe estar en revisión por VGO o Administración.',
            ]);
        }

        if ($role === 'super_admin') {
            return;
        }

        if ($role === 'vgo' && $solicitud->status === 'vgo') {
            return;
        }

        if ($role === 'administracion' && $solicitud->status === 'administracion') {
            return;
        }

        throw ValidationException::withMessages([
            'action' => 'No tienes permisos para actualizar esta solicitud.',
        ]);
    }

    protected function assertCanFinalize(string $role, Solicitud $solicitud): void
    {
        if (!in_array($role, ['super_admin', 'normalizacion'], true)) {
            throw ValidationException::withMessages([
                'action' => 'No tienes permisos para finalizar esta solicitud.',
            ]);
        }

        if (!in_array($solicitud->status, ['vgo', 'administracion', 'pendiente'], true)) {
            throw ValidationException::withMessages([
                'action' => 'La solicitud no puede ser finalizada en el estatus actual.',
            ]);
        }
    }

    protected function releaseReservation(Inventory $inventory, Solicitud $solicitud): void
    {
        $inventory->reserved_quantity = max(0, $inventory->reserved_quantity - $solicitud->quantity);
        $inventory->save();
    }

    protected function consumeInventory(Inventory $inventory, Solicitud $solicitud): void
    {
        if ($inventory->reserved_quantity < $solicitud->quantity) {
            throw ValidationException::withMessages([
                'action' => 'La cantidad reservada es inferior a la requerida para aprobar la solicitud.',
            ]);
        }

        if ($inventory->quantity < $solicitud->quantity) {
            throw ValidationException::withMessages([
                'action' => 'El inventario disponible es insuficiente para aprobar la solicitud.',
            ]);
        }

        $inventory->quantity = max(0, $inventory->quantity - $solicitud->quantity);
        $inventory->reserved_quantity -= $solicitud->quantity;
        $inventory->save();
    }

    protected function createStatusLog(Solicitud $solicitud, string $action, string $status, ?string $justification = null): void
    {
        SolicitudStatusLog::create([
            'solicitud_id' => $solicitud->id,
            'status' => $status,
            'action' => $action,
            'justification' => $justification,
            'changed_by' => Auth::id(),
        ]);
    }
}
