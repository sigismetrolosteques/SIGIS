<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ProductController extends Controller
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
            'ubicacionOptions' => $this->ubicacionOptions,
        ]);
    }

   public function store(Request $request)
{
    $this->authorizeManagement();

    $request->validate([
        'name' => 'required|string|max:255',
        'sub_area' => 'nullable|string|max:255',
        'ubicaciones' => 'nullable|array',
        'ubicaciones.*' => 'string',
        'tipo_material' => 'required|string|in:equipos,herramientas,consumibles',
        'marcas_sugeridas' => 'nullable|string',
        'modelo' => 'nullable|string|max:255',
        'dimensiones' => 'nullable|string|max:255',
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
        'ubicaciones' => $this->filterUbicaciones($request->input('ubicaciones', [])),
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
            'ubicacionOptions' => $this->ubicacionOptions,
        ]);
    }

   public function update(Request $request, Product $product)
{
    $this->authorizeManagement();

    $request->validate([
        'name' => 'required|string|max:255',
        'sub_area' => 'nullable|string|max:255',
        'ubicaciones' => 'nullable|array',
        'ubicaciones.*' => 'string',
        'tipo_material' => 'required|string|in:equipos,herramientas,consumibles',
        'marcas_sugeridas' => 'nullable|string',
        'modelo' => 'nullable|string|max:255',
        'dimensiones' => 'nullable|string|max:255',
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
    $product->ubicaciones = $this->filterUbicaciones($request->input('ubicaciones', []));
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

    protected function filterUbicaciones($ubicaciones): array
    {
        if (!is_array($ubicaciones)) {
            return [];
        }

        $filtered = array_intersect($ubicaciones, $this->ubicacionOptions);

        return array_values(array_unique($filtered));
    }
}
