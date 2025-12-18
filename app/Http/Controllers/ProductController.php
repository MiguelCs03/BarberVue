<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        // 1. Filtro por Nombre
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        // 2. Filtro por Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // 3. Filtro por Stock Bajo (Usando tu lógica isLowStock)
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'bajo') {
                $query->whereRaw('stock_actual <= stock_minimo');
            } elseif ($request->stock_status === 'normal') {
                $query->whereRaw('stock_actual > stock_minimo');
            }
        }

        
        $productos = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn($prod) => [
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'descripcion' => $prod->descripcion,
                'precio_venta' => $prod->precio_venta,
                'stock_actual' => $prod->stock_actual,
                'stock_minimo' => $prod->stock_minimo,
                'estado' => $prod->estado,
                'es_stock_bajo' => $prod->isLowStock(), 
            ]);

        return Inertia::render('Products/Index', [
            'productos' => $productos,
            'filters' => $request->only(['search', 'estado', 'stock_status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        Producto::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(string $id)
    {
        // Buscamos el producto o lanzamos 404
        $product = Producto::findOrFail($id);

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'nombre' => $product->nombre,
                'descripcion' => $product->descripcion,
                'estado' => $product->estado,
                'precio_venta' => (float) $product->precio_venta,
                'stock_actual' => (int) $product->stock_actual,
                'stock_minimo' => (int) $product->stock_minimo,
                'es_stock_bajo' => $product->isLowStock(), // Usamos tu función del modelo
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ]);
    }

  public function edit(string $id)
  {
        $producto = Producto::findOrFail($id);

        return Inertia::render('Products/Edit', [
            'product' => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'precio_venta' => (float) $producto->precio_venta,
            'stock_minimo' => (int) $producto->stock_minimo,
            'estado' => $producto->estado,
            'stock_actual' => $producto->stock_actual, 
        ]
     ]);
  }

    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'estado'       => 'required|in:activo,inactivo',
        ]);

        $producto->update($validated);

        return redirect()->back()
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
