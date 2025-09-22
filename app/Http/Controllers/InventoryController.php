<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
   public function index()
{
    $inventories = Inventory::with('product')->latest()->paginate(10);
    return Inertia::render('Inventory/Index', [
        'inventories' => $inventories 
    ]);
}


    public function create()
    {
        $products = Product::all();
        return Inertia::render('Inventory/Create', [
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        Inventory::create($request->all());

        return redirect()->route('inventory.index')->with('success', 'Registro de inventario creado');
    }

    public function edit(Inventory $inventory)
    {
        $products = Product::all();
        return Inertia::render('Inventory/Edit', [
            'inventory' => $inventory,
            'products' => $products
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $inventory->update($request->all());

        return redirect()->route('inventory.index')->with('success', 'Registro de inventario actualizado');
    }

    public function show($id)
{
    $inventory = Inventory::with('product')->findOrFail($id);

    return Inertia::render('Inventory/Show', [
        'inventory' => $inventory,
    ]);
}
    
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Registro eliminado');
    }
}
