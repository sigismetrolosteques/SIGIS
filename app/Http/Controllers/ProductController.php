<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return Inertia::render('Product/Index', [
            'products' => $products
        ]);
    }

    public function create()
    {
        return Inertia::render('Product/Create');
    }

   public function store(Request $request)
{
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
        'name' => $request->name,
        'image' => $imageName, // solo el nombre
        'description' => $request->description,
    ]);

    return redirect()->route('product.index')->with('success', 'Producto creado correctamente');
}


    public function edit(Product $product)
    {
        return Inertia::render('Product/Edit', [
            'product' => $product
        ]);
    }

   public function update(Request $request, Product $product)
{
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
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('product.index')->with('success', 'Producto eliminado correctamente');
    }
}
