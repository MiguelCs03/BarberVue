<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $movimientos = MovimientoInventario::with('producto')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'producto_nombre' => $movimiento->producto->nombre,
                    'tipo_movimiento' => $movimiento->tipo_movimiento,
                    'cantidad' => $movimiento->cantidad,
                    'motivo' => $movimiento->motivo,
                    'created_at' => $movimiento->created_at->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Inventory/Index', [
            'movimientos' => $movimientos,
        ]);
    }

    public function create()
    {
        $productos = Producto::orderBy('nombre')->get()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'stock_actual' => $producto->stock_actual,
            ];
        });

        return Inertia::render('Inventory/Create', [
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_movimiento' => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        // Validate stock for salida
        if ($validated['tipo_movimiento'] === 'salida') {
            $producto = Producto::find($validated['producto_id']);
            if ($producto->stock_actual < $validated['cantidad']) {
                return back()->withErrors([
                    'cantidad' => 'No hay suficiente stock disponible. Stock actual: ' . $producto->stock_actual
                ]);
            }
        }

        MovimientoInventario::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Movimiento registrado exitosamente');
    }

    public function show(string $id)
    {
        $movimiento = MovimientoInventario::with('producto')->findOrFail($id);

        $movimientoData = [
            'id' => $movimiento->id,
            'producto' => [
                'id' => $movimiento->producto->id,
                'nombre' => $movimiento->producto->nombre,
                'stock_actual' => $movimiento->producto->stock_actual,
            ],
            'tipo_movimiento' => $movimiento->tipo_movimiento,
            'cantidad' => $movimiento->cantidad,
            'motivo' => $movimiento->motivo,
            'created_at' => $movimiento->created_at->format('d/m/Y H:i'),
        ];

        return Inertia::render('Inventory/Show', [
            'movimiento' => $movimientoData,
        ]);
    }
}
