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
use Barryvdh\DomPDF\Facade\Pdf;

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
            'product_id' => 'required|exists:products,id',
            'inventory_id' => 'required|exists:inventories,id',
            'quantity' => 'required|integer|min:1',
            'to_gerencia_id' => 'required|exists:gerencias,id',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $inventory = Inventory::find($data['inventory_id']);

        if (!$inventory) return back()->withErrors(['inventory_id'=>'Inventario no válido']);
        if ($inventory->product_id != $data['product_id'])
            return back()->withErrors(['inventory_id'=>'El inventario seleccionado no pertenece al producto elegido']);
        if ($data['quantity'] > ($inventory->quantity - $inventory->reserved_quantity))
            return back()->withErrors(['quantity'=>'La cantidad solicitada supera el stock disponible']);

        DB::beginTransaction();
        try {
            // Crear solicitud
            $solicitud = Solicitud::create([
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

            // Reservar cantidad en inventario
            $inventory->reserved_quantity += $data['quantity'];
            $inventory->save();

            DB::commit();
            return redirect()->route('solicitudes.index')->with('success','Solicitud creada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Ocurrió un error al crear la solicitud']);
        }
    }

    // Actualizar estado (aprobar / rechazar / pendiente)
    public function updateStatus(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'status' => 'required|in:pendiente,aprobado,rechazado',
        ]);

        $inventory = Inventory::find($solicitud->inventory_id);
        if (!$inventory) return back()->withErrors(['inventory'=>'Inventario no encontrado']);

        DB::beginTransaction();
        try {
            if ($data['status'] === 'aprobado') {
                if ($solicitud->status !== 'aprobado') {
                    // Descontar stock y liberar reserva
                    $inventory->quantity -= $solicitud->quantity;
                    $inventory->reserved_quantity -= $solicitud->quantity;
                    $inventory->save();

                    $solicitud->status = 'aprobado';
                    $solicitud->save();
                }
            } elseif ($data['status'] === 'rechazado') {
                if ($solicitud->status === 'pendiente') {
                    // Liberar cantidad reservada
                    $inventory->reserved_quantity -= $solicitud->quantity;
                    $inventory->save();

                    $solicitud->status = 'rechazado';
                    $solicitud->save();
                }
            } else { // volver a pendiente
                if ($solicitud->status !== 'pendiente') {
                    // Reservar cantidad nuevamente
                    $inventory->reserved_quantity += $solicitud->quantity;
                    $inventory->save();

                    $solicitud->status = 'pendiente';
                    $solicitud->save();
                }
            }

            DB::commit();
            return back()->with('success','Solicitud actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general'=>'Ocurrió un error al actualizar la solicitud']);
        }
    }

    // Generar PDF
    public function generatePDF($id)
    {
        $solicitud = Solicitud::with(['product'])->findOrFail($id);
        $pdf = Pdf::loadView('solicitudes.pdf', compact('solicitud'));
        return $pdf->stream("minuta_{$solicitud->code}.pdf");
    }
}
