<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class InventoryController extends Controller
{
    protected array $tipoMaterialOptions = [
        ['value' => 'equipos', 'label' => 'Equipos'],
        ['value' => 'herramientas', 'label' => 'Herramientas'],
        ['value' => 'consumibles', 'label' => 'Consumibles'],
    ];

    protected array $ubicacionOptions = [
        'Almacén de mantenimiento Piso 3 CECAP',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipoMaterial = $request->input('tipo_material');

        $inventories = Inventory::with('product')
            ->when($search, function ($query, $value) {
                $query->where(function ($sub) use ($value) {
                    $sub->where('location', 'like', '%' . $value . '%')
                        ->orWhere('notes', 'like', '%' . $value . '%')
                        ->orWhereHas('product', function ($productQuery) use ($value) {
                            $productQuery->where('name', 'like', '%' . $value . '%')
                                ->orWhere('code', 'like', '%' . $value . '%');
                        });
                });
            })
            ->when($tipoMaterial, function ($query, $value) {
                $allowed = array_values(array_intersect(Arr::wrap($value), ['equipos', 'herramientas', 'consumibles']));
                if (!empty($allowed)) {
                    $query->whereHas('product', function ($productQuery) use ($allowed) {
                        $productQuery->whereIn('tipo_material', $allowed);
                    });
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'filters' => [
                'search' => $search,
                'tipo_material' => $tipoMaterial,
            ],
            'canManage' => $request->user()->hasAnyRole(['super_admin', 'admin']),
            'tipoMaterialOptions' => $this->tipoMaterialOptions,
        ]);
    }

    public function create()
    {
        $this->authorizeManagement();

        return Inertia::render('Inventory/Create', [
            'products' => Product::all(),
            'tipoMaterialOptions' => $this->tipoMaterialOptions,
            'ubicacionOptions' => $this->ubicacionOptions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'ubicaciones' => 'nullable|array',
            'ubicaciones.*' => 'string',
            'notes' => 'nullable|string',
        ]);

        Inventory::create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'location' => $this->resolveLocation($request) ?? ($validated['location'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'reserved_quantity' => 0,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Registro de inventario creado');
    }

    public function edit(Inventory $inventory)
    {
        $this->authorizeManagement();

        return Inertia::render('Inventory/Edit', [
            'inventory' => $inventory,
            'products' => Product::all(),
            'tipoMaterialOptions' => $this->tipoMaterialOptions,
            'ubicacionOptions' => $this->ubicacionOptions,
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'ubicaciones' => 'nullable|array',
            'ubicaciones.*' => 'string',
            'notes' => 'nullable|string',
        ]);

        $inventory->update([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'location' => $this->resolveLocation($request) ?? ($validated['location'] ?? null),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Registro de inventario actualizado');
    }

    public function show($id, Request $request)
    {
        $inventory = Inventory::with('product')->findOrFail($id);

        return Inertia::render('Inventory/Show', [
            'inventory' => $inventory,
            'canManage' => $request->user()->hasAnyRole(['super_admin', 'admin']),
        ]);
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorizeManagement();

        $hasRequests = Solicitud::where('inventory_id', $inventory->id)->exists();
        if ($hasRequests) {
            return back()->withErrors([
                'inventory' => 'No es posible eliminar el registro porque tiene solicitudes asociadas.',
            ]);
        }

        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Registro eliminado');
    }

    protected function authorizeManagement(): void
    {
        if (!auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'No tienes permiso para gestionar inventario');
        }
    }

    protected function resolveLocation(Request $request): ?string
    {
        $selected = $this->filterUbicaciones($request->input('ubicaciones', []));
        if (!empty($selected)) {
            return implode(', ', $selected);
        }

        $location = $request->input('location');
        return $location !== null && trim($location) !== '' ? trim($location) : null;
    }

    protected function filterUbicaciones($ubicaciones): array
    {
        if (!is_array($ubicaciones)) {
            return [];
        }

        $filtered = array_intersect($ubicaciones, $this->ubicacionOptions);

        return array_values(array_unique($filtered));
    }
}
