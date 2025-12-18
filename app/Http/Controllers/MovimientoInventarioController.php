<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovimientoInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Preparar la consulta con la relación del producto
        $query = MovimientoInventario::with(['producto:id,nombre'])
            ->latest(); // Ordenar por created_at descendente

        // 2. Aplicar Filtros Condicionales
        
        // Filtro por nombre de producto (búsqueda)
        if ($request->filled('search')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por Tipo de Movimiento (entrada, salida, ajuste)
        if ($request->filled('tipo')) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        // Filtro por Estado (activo, anulado)
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }  
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        // 3. Paginación y transformación de datos
        $movimientos = $query->paginate(10)
            ->withQueryString()
            ->through(fn ($mov) => [
                'id' => $mov->id,
                'producto_nombre' => $mov->producto->nombre,
                'tipo_movimiento' => $mov->tipo_movimiento,
                'cantidad' => $mov->cantidad,
                'estado' => $mov->estado,
                'motivo' => $mov->motivo,
                'fecha' => $mov->created_at->format('d/m/Y H:i'),
            ]);

        // 4. Datos para los selectores del frontend (Filtros)
        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos,
            'filters' => $request->only(['search', 'tipo', 'estado','desde','hasta']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
