<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $productos = Producto::orderBy('nombre')->get()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_venta' => $producto->precio_venta,
                'stock_actual' => $producto->stock_actual,
                'stock_minimo' => $producto->stock_minimo,
                'is_low_stock' => $producto->isLowStock(),
            ];
        });

        return Inertia::render('Products/Index', [
            'productos' => $productos,
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
        $producto = Producto::with('movimientos')->findOrFail($id);

        $productoData = [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'precio_venta' => $producto->precio_venta,
            'stock_actual' => $producto->stock_actual,
            'stock_minimo' => $producto->stock_minimo,
            'is_low_stock' => $producto->isLowStock(),
            'created_at' => $producto->created_at->format('d/m/Y H:i'),
            'movimientos' => $producto->movimientos->map(function ($mov) {
                return [
                    'id' => $mov->id,
                    'tipo_movimiento' => $mov->tipo_movimiento,
                    'cantidad' => $mov->cantidad,
                    'motivo' => $mov->motivo,
                    'created_at' => $mov->created_at->format('d/m/Y H:i'),
                ];
            }),
        ];

        return Inertia::render('Products/Show', [
            'producto' => $productoData,
        ]);
    }

    public function edit(string $id)
    {
        $producto = Producto::findOrFail($id);

        return Inertia::render('Products/Edit', [
            'producto' => $producto,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        $producto->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
