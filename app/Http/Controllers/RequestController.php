<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Product;
use App\Models\Gerencia;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // Listado de solicitudes
    public function index()
    {
        $solicitudes = Solicitud::with(['product', 'fromGerencia', 'toGerencia', 'creator'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Solicitudes/Index', [
            'solicitudes' => $solicitudes
        ]);
    }

    // Formulario de creación
    public function create()
    {
        $products = Product::all();
        $gerencias = Gerencia::all();
        // pasar inventarios también para poblar select (filtraremos en frontend)
        $inventories = Inventory::all();

        return Inertia::render('Solicitudes/Create', [
            'products' => $products,
            'gerencias' => $gerencias,
            'inventories' => $inventories,
        ]);
    }

    // Guardar nueva solicitud
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'inventory_id' => 'required|exists:inventories,id',
            'quantity' => 'required|integer|min:1',
            'to_gerencia_id' => 'required|exists:gerencias,id',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        // comprobar stock disponible
        $inventory = Inventory::find($data['inventory_id']);
        if (!$inventory) {
            return back()->withErrors(['inventory_id' => 'Inventario no válido']);
        }

        if ($inventory->product_id != $data['product_id']) {
            return back()->withErrors(['inventory_id' => 'El inventario seleccionado no pertenece al producto elegido']);
        }

        if ($data['quantity'] > $inventory->quantity) {
            return back()->withErrors(['quantity' => 'La cantidad solicitada supera el stock disponible']);
        }

        // crear solicitud (no modificamos inventario aún; se resta cuando se apruebe)
        Solicitud::create([
            'code' => strtoupper(uniqid('REQ-')),
            'product_id' => $data['product_id'],
            'inventory_id' => $data['inventory_id'],
            'quantity' => $data['quantity'],
            'created_by' => $user->id,
            'from_gerencia_id' => $user->gerencia_id ?? null,
            'to_gerencia_id' => $data['to_gerencia_id'],
            'status' => 'pendiente',
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud creada correctamente');
    }

    // Actualizar estado (aprobar / rechazar)
    public function updateStatus(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'status' => 'required|in:pendiente,aprobado,rechazado',
        ]);

        // Si no hay inventario relacionado y se aprueba, no podemos restar
        if ($data['status'] === 'aprobado') {
            // si ya está aprobado, no restamos otra vez
            if ($solicitud->status === 'aprobado') {
                return back()->with('info', 'La solicitud ya está aprobada.');
            }

            // Hacer la operación en transacción
            DB::beginTransaction();
            try {
                $inventory = Inventory::find($solicitud->inventory_id);

                if (!$inventory) {
                    DB::rollBack();
                    return back()->withErrors(['inventory' => 'Inventario asociado no encontrado.']);
                }

                // Verificar stock suficiente
                if ($inventory->quantity < $solicitud->quantity) {
                    DB::rollBack();
                    return back()->withErrors(['quantity' => 'Stock insuficiente para aprobar la solicitud.']);
                }

                // Restar stock
                $inventory->quantity = $inventory->quantity - $solicitud->quantity;
                $inventory->save();

                // Actualizar estado de la solicitud
                $solicitud->status = 'aprobado';
                $solicitud->save();

                DB::commit();
                return back()->with('success', 'Solicitud aprobada y stock actualizado.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['general' => 'Ocurrió un error al aprobar la solicitud.']);
            }
        } else {
            // Si es rechazar o volver a pendiente, simplemente actualizamos estado.
            $solicitud->status = $data['status'];
            $solicitud->save();

            return back()->with('success', 'Solicitud actualizada correctamente');
        }
    }
}
