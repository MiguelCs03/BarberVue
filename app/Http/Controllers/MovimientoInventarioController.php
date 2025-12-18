<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->through(fn($mov) => [
                'id' => $mov->id,
                'producto_nombre' => $mov->producto->nombre,
                'tipo_movimiento' => $mov->tipo_movimiento,
                'cantidad' => $mov->cantidad,
                'estado' => $mov->estado,
                'motivo' => $mov->motivo,
                'fecha' => $mov->fecha->format('d/m/Y H:i'),
            ]);

        // 4. Datos para los selectores del frontend (Filtros)
        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos,
            'filters' => $request->only(['search', 'tipo', 'estado', 'desde', 'hasta']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Movimientos/CreateMovimiento', [
            'productos' => Producto::select('id', 'nombre', 'stock_actual')->get(),
            'tipos' => ['entrada', 'salida', 'ajuste'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'tipo_movimiento' => 'required|in:entrada,salida,ajuste',
            'cantidad'        => 'required|integer', 
            'motivo'          => 'nullable|string|max:255',
            'fecha'           => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::lockForUpdate()->find($validated['producto_id']);
            $cantidad = (int) $validated['cantidad'];
            $tipo = $validated['tipo_movimiento'];

            $cambioStock = 0;

            if ($tipo === 'entrada') {
                $cambioStock = abs($cantidad);
            } elseif ($tipo === 'salida') {
                if ($cantidad <= 0) {
                    return back()->withErrors(['cantidad' => 'Para salidas, la cantidad debe ser un número positivo.'])->withInput();
                }
                $cambioStock = -abs($cantidad);
            } else { // Ajuste
                $cambioStock = $cantidad; 
            }

            if (($producto->stock_actual + $cambioStock) < 0) {
                return back()->withErrors(['cantidad' => 'Stock insuficiente. El stock actual de {$producto->nombre} es {$producto->stock_actual}.'])->withInput();
            }
            //validacion de estock minimom
            if(($producto->stock_minimo > $producto->stock_actual + $cambioStock) ){
                return back()->withErrors(['cantidad' => 'Stock insuficiente. El stock actual de {$producto->nombre} es {$producto->stock_actual}.'])->withInput();
            }
            // 4. Crear el Movimiento
            MovimientoInventario::create([
                'producto_id'     => $validated['producto_id'],
                'tipo_movimiento' => $tipo,
                'cantidad'        => $cantidad,
                'motivo'          => $validated['motivo'],
                'fecha'           => $validated['fecha'],
            ]);

            // 5. Actualizar Stock del Producto
            $producto->stock_actual += $cambioStock;
            $producto->save();

            // Todo bien, confirmamos cambios
            DB::commit();

            return redirect()->back()
                ->with('success', "Movimiento registrado con éxito. Stock actualizado.");

        } catch (Exception $e) {
            // Algo falló, revertimos todo
            DB::rollBack();

            return back()->withErrors(['cantidad' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $movimiento = MovimientoInventario::with('producto')->findOrFail($id);

        return Inertia::render('Movimientos/ShowMovimiento', [
            'movimiento' => [
                'id'              => $movimiento->id,
                'producto_id'      => $movimiento->producto_id,
                'producto_nombre'  => $movimiento->producto->nombre,
                'tipo_movimiento'  => $movimiento->tipo_movimiento,
                'cantidad'         => $movimiento->cantidad,
                'estado'           => $movimiento->estado,
                'motivo'           => $movimiento->motivo,
                'fecha'            => $movimiento->fecha, 
                'created_at'       => $movimiento->created_at,
                'updated_at'       => $movimiento->updated_at,
            ]
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $movimiento = MovimientoInventario::with('producto:id,nombre')->findOrFail($id);

        // Si el movimiento está anulado, no debería editarse
        if ($movimiento->estado === 'anulado') {
            return redirect()->route('movimientos.show', $id)
                ->with('error', 'No se puede editar un movimiento anulado.');
        }

        return Inertia::render('Movimientos/EditMovimiento', [
            'movimiento' => [
                'id' => $movimiento->id,
                'producto_nombre' => $movimiento->producto->nombre,
                'tipo_movimiento' => $movimiento->tipo_movimiento,
                'cantidad' => $movimiento->cantidad,
                'fecha' => Carbon::parse($movimiento->fecha)->format('Y-m-d\TH:i'), 
                'motivo' => $movimiento->motivo,
            ]
        ]);
    }

    public function update(Request $request, string $id)
    {
        $movimiento = MovimientoInventario::findOrFail($id);

        // Seguridad: Evitar edición de anulados
        if ($movimiento->estado === 'anulado') {
            return back()->with('error', 'Acción no permitida: El registro está anulado.');
        }

        $validated = $request->validate([
            'fecha' => 'required|date',
            'motivo' => 'nullable|string|max:255',
        ]);

        $movimiento->update($validated);

        return redirect()->route('movimientos.show', $id)
            ->with('success', 'Movimiento actualizado correctamente.');
    }

    public function anular(string $id)
    {
        DB::beginTransaction();

        try {
            
            $movimiento = MovimientoInventario::findOrFail($id);

            if ($movimiento->estado === 'anulado') {
                return back()->with('error', 'Acción no permitida: El registro está anulado.');
            }

            $producto = Producto::lockForUpdate()->find($movimiento->producto_id);
            $cantidad = (int) $movimiento->cantidad;
            $tipo = $movimiento->tipo_movimiento;

            
            $reversionStock = 0;

            if ($tipo === 'entrada') {
                $reversionStock = -abs($cantidad);
            } elseif ($tipo === 'salida') {
                $reversionStock = abs($cantidad);
            } else { // Ajuste
                $reversionStock = -$cantidad;
            }

            if (($producto->stock_actual + $reversionStock) < 0) {
                return back()->with('error', 'No se puede anular: El stock resultante de ' . $producto->nombre . ' sería negativo (' . $producto->stock_actual . ' + ' . $reversionStock . ').');
            }
            if(($producto->stock_minimo > $producto->stock_actual + $reversionStock) ){
                return back()->withErrors(['cantidad' => 'Stock insuficiente. El stock actual de {$producto->nombre} es {$producto->stock_actual}.'])->withInput();
            }

            // 4. Ejecutar la reversión
            $producto->stock_actual += $reversionStock;
            $producto->save();

            // 5. Marcar movimiento como anulado
            $movimiento->update(['estado' => 'anulado']);

            DB::commit();

            return back()->with('success', 'Movimiento anulado correctamente. El stock ha sido revertido.');

        } catch (Exception $e) {
            DB::rollBack();
            
            // Usamos SweetAlert o flash message para mostrar el error de la excepción
            return back()->with('error', $e->getMessage());
        }
    }
    /**
     * Update the specified resource in storage.
     */
   

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $movimiento = MovimientoInventario::findOrFail($id);

        // Si se elimina un movimiento activo, deberíamos anularlo primero para revertir stock
        if ($movimiento->estado === 'activo') {
            $movimiento->estado = 'anulado';
            $movimiento->save();
        }

        $movimiento->delete();

        return redirect()->route('movimientos.index')
            ->with('success', 'Movimiento eliminado.');
    }
}
