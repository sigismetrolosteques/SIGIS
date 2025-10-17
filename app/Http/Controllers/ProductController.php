<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::query()
            ->when($search, function ($query, $value) {
                $query->where(function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%')
                        ->orWhere('code', 'like', '%' . $value . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Product/Index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
            ],
            'canManage' => $request->user()->hasAnyRole(['super_admin', 'admin']),
        ]);
    }

    public function create()
    {
        $this->authorizeManagement();

        return Inertia::render('Product/Create');
    }

   public function store(Request $request)
{
    $this->authorizeManagement();

    $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'required|image|max:2048',
        'description' => 'nullable|string',
    ]);

    // Guardar imagen en public/img
    if ($request->hasFile('image')) {
        $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
        $request->file('image')->move(public_path('img'), $imageName);
    }

    Product::create([
        'code' => $this->generateProductCode(),
        'name' => $request->name,
        'image' => $imageName, // solo el nombre
        'description' => $request->description,
    ]);

    return redirect()->route('product.index')->with('success', 'Producto creado correctamente');
    }


    public function edit(Product $product)
    {
        $this->authorizeManagement();

        return Inertia::render('Product/Edit', [
            'product' => $product
        ]);
    }

   public function update(Request $request, Product $product)
{
    $this->authorizeManagement();

    $request->validate([
        'name' => 'required|string|max:255',
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
}
