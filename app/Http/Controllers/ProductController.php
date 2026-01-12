<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    protected array $tipoMaterialOptions = [
        ['value' => 'equipos', 'label' => 'Equipos'],
        ['value' => 'herramientas', 'label' => 'Herramientas'],
        ['value' => 'consumibles', 'label' => 'Consumibles'],
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipoMaterial = $request->input('tipo_material');

        $products = Product::query()
            ->when($search, function ($query, $value) {
                $query->where(function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%')
                        ->orWhere('code', 'like', '%' . $value . '%');
                });
            })
            ->when($tipoMaterial, function ($query, $value) {
                $allowed = collect(Arr::wrap($value))->filter(fn ($item) => in_array($item, ['equipos', 'herramientas', 'consumibles'], true));
                if ($allowed->isNotEmpty()) {
                    $query->whereIn('tipo_material', $allowed->all());
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Product/Index', [
            'products' => $products,
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

        return Inertia::render('Product/Create', [
            'tipoMaterialOptions' => $this->tipoMaterialOptions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'sub_area' => 'nullable|string|max:255',
            'tipo_material' => 'required|string|in:equipos,herramientas,consumibles',
            'marcas_sugeridas' => 'nullable|string',
            'modelo' => 'nullable|string|max:255',
            'dimensiones' => 'nullable|string',
            'especificaciones_tecnicas' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'description' => 'nullable|string',
        ]);

        // Guardar imagen en public/img
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('img'), $imageName);
        }

        Product::create([
            'code' => $this->generateProductCode(),
            'name' => $request->name,
            'sub_area' => $request->sub_area,
            'tipo_material' => $request->tipo_material,
            'marcas_sugeridas' => $request->marcas_sugeridas,
            'modelo' => $request->modelo,
            'dimensiones' => $request->dimensiones,
            'especificaciones_tecnicas' => $request->especificaciones_tecnicas,
            'image' => $imageName, // solo el nombre
            'description' => $request->description,
        ]);

        return redirect()->route('product.index')->with('success', 'Producto creado correctamente');
    }

    public function edit(Product $product)
    {
        $this->authorizeManagement();

        return Inertia::render('Product/Edit', [
            'product' => $product,
            'tipoMaterialOptions' => $this->tipoMaterialOptions,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'sub_area' => 'nullable|string|max:255',
            'tipo_material' => 'required|string|in:equipos,herramientas,consumibles',
            'marcas_sugeridas' => 'nullable|string',
            'modelo' => 'nullable|string|max:255',
            'dimensiones' => 'nullable|string',
            'especificaciones_tecnicas' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            // Borrar imagen anterior si existe
            if ($product->image && file_exists(public_path('img/' . $product->image))) {
                unlink(public_path('img/' . $product->image));
            }

            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('img'), $imageName);
            $product->image = $imageName;
        }

        $product->name = $request->name;
        $product->sub_area = $request->sub_area;
        $product->tipo_material = $request->tipo_material;
        $product->marcas_sugeridas = $request->marcas_sugeridas;
        $product->modelo = $request->modelo;
        $product->dimensiones = $request->dimensiones;
        $product->especificaciones_tecnicas = $request->especificaciones_tecnicas;
        $product->description = $request->description;
        $product->save();

        return redirect()->route('product.index')->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Product $product)
    {
        $this->authorizeManagement();

        $hasInventories = $product->inventories()->exists();
        $hasRequests = Solicitud::where('product_id', $product->id)->exists();

        if ($hasInventories || $hasRequests) {
            return back()->withErrors([
                'product' => 'No es posible eliminar el producto porque tiene inventario o solicitudes asociadas.',
            ]);
        }

        if ($product->image && file_exists(public_path('img/' . $product->image))) {
            unlink(public_path('img/' . $product->image));
        }

        $product->delete();

        return redirect()->route('product.index')->with('success', 'Producto eliminado correctamente');
    }

    public function show(Product $product)
    {
        return Inertia::render('Product/Show', [
            'product' => $product,
            'canManage' => auth()->user()->hasAnyRole(['super_admin', 'admin']),
        ]);
    }

    public function exportPdf(Product $product)
    {
        $product->load('inventories');

        $totals = [
            'quantity' => $product->inventories->sum('quantity'),
            'reserved' => $product->inventories->sum('reserved_quantity'),
        ];
        $totals['available'] = max(0, $totals['quantity'] - $totals['reserved']);

        $pdf = Pdf::loadView('productos.ficha_pdf', [
            'product' => $product,
            'totals' => $totals,
        ]);

        return $pdf->stream("ficha_producto_{$product->code}.pdf");
    }

    protected function authorizeManagement(): void
    {
        if (!auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'No tienes permiso para gestionar productos');
        }
    }

    protected function generateProductCode(): string
    {
        do {
            $code = strtoupper(uniqid('PRD-'));
        } while (Product::where('code', $code)->exists());

        return $code;
    }
}
