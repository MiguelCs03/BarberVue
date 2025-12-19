<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Detalle;
use App\Models\DetallePago;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\TipoPago;
use App\Models\HorarioBarbero;
use App\Models\ExcepcionHorario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente.usuario', 'barbero.usuario', 'detallePagos']);

        if ($request->filled('search')) {
            $query->whereHas('cliente.usuario', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $ventas = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function($venta) {
                $totalPagado = $venta->detallePagos->sum('monto');
                return [
                    'id' => $venta->id,
                    'cliente' => $venta->cliente->usuario->name ?? 'N/A',
                    'barbero' => $venta->barbero->usuario->name ?? 'N/A',
                    'monto_total' => (float)$venta->monto_total,
                    'total_pagado' => (float)$totalPagado,
                    'estado_pago' => $venta->estado_pago,
                    'fecha' => $venta->created_at->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ventas/Create', [
            'clientes' => Cliente::with('usuario:id,name')->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->usuario->name,
            ]),
            'productos' => Producto::where('estado', 'activo')->get(['id', 'nombre', 'precio_venta', 'stock_actual']),
            'tiposPago' => TipoPago::all(['id', 'nombre']),
            'barberos' => Barbero::with(['usuario:id,name', 'servicioBarberos.servicio'])->get()->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->usuario->name,
                'servicios' => $b->servicioBarberos->map(fn($sb) => [
                    'id' => $sb->servicio->id,
                    'nombre' => $sb->servicio->nombre,
                    'precio' => (float)$sb->servicio->precio,
                ]),
            ]),
            'servicios' => Servicio::all(['id', 'nombre', 'precio']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'monto_total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:producto,servicio',
            'items.*.id' => 'required',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'required|numeric|min:0',
            'items.*.barbero_id' => 'nullable|exists:barberos,id',
            'items.*.fecha' => 'nullable|date',
            'items.*.hora' => 'nullable|date_format:H:i',
            'pagos' => 'nullable|array',
            'pagos.*.tipo_pago_id' => 'required|exists:tipo_pagos,id',
            'pagos.*.monto' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $montoTotal = $validated['monto_total'];
            
            // Validate services, barberos and availability
            $mainFechaHora = null;
            $mainBarberoId = null;

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'servicio') {
                    if (empty($item['barbero_id'])) {
                        throw new \Exception('Debe seleccionar un barbero para cada servicio.');
                    }
                    if (empty($item['fecha']) || empty($item['hora'])) {
                        throw new \Exception('Debe programar la fecha y hora para cada servicio.');
                    }
                    
                    $itemFechaHora = Carbon::parse($item['fecha'] . ' ' . $item['hora']);
                    
                    // Set main sale info from the first service found
                    if (!$mainFechaHora) {
                        $mainFechaHora = $itemFechaHora;
                        $mainBarberoId = $item['barbero_id'];
                    }

                    // Validate barbero-service association
                    $offersService = DB::table('servicio_barberos')
                        ->where('barbero_id', $item['barbero_id'])
                        ->where('servicio_id', $item['id'])
                        ->exists();
                    
                    if (!$offersService) {
                        throw new \Exception('Uno de los barberos seleccionados no ofrece el servicio asignado.');
                    }

                    // Validate barbero availability
                    if (!$this->esBarberoDisponibleEnHorario($item['barbero_id'], $itemFechaHora)) {
                        $barbero = Barbero::with('usuario')->find($item['barbero_id']);
                        $barberoName = $barbero->usuario->name ?? 'Desconocido';
                        throw new \Exception("El barbero {$barberoName} no está disponible en el horario seleccionado ({$item['fecha']} {$item['hora']}).");
                    }
                }
            }

            $totalPagado = collect($validated['pagos'] ?? [])->sum('monto');
            
            $estadoPago = 'pendiente';
            if ($totalPagado >= $montoTotal) {
                $estadoPago = 'completado';
            } elseif ($totalPagado > 0) {
                $estadoPago = 'parcial';
            }

            $venta = new Venta([
                'cliente_id' => $validated['cliente_id'],
                'barbero_id' => $mainBarberoId,
                'monto_total' => $montoTotal,
                'estado_pago' => $estadoPago,
            ]);
            
            // Use service time as sale creation time if available
            if ($mainFechaHora) {
                $venta->created_at = $mainFechaHora;
            }
            $venta->save();

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'producto') {
                    $producto = Producto::findOrFail($item['id']);
                    
                    if ($producto->stock_actual < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para el producto: {$producto->nombre}");
                    }

                    $producto->decrement('stock_actual', $item['cantidad']);
                    
                    Detalle::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $item['cantidad'] * $item['precio'],
                    ]);
                } else {
                    Detalle::create([
                        'venta_id' => $venta->id,
                        'servicio_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $item['cantidad'] * $item['precio'],
                    ]);
                }
            }

            if (!empty($validated['pagos'])) {
                foreach ($validated['pagos'] as $pago) {
                    DetallePago::create([
                        'venta_id' => $venta->id,
                        'tipo_pago_id' => $pago['tipo_pago_id'],
                        'monto' => $pago['monto'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta registrada exitosamente con estado: ' . $estadoPago);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar venta: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente.usuario',
            'barbero.usuario',
            'detalles.producto',
            'detalles.servicio',
            'detallePagos.tipoPago'
        ]);

        $totalPagado = $venta->detallePagos->sum('monto');

        return Inertia::render('Ventas/Show', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente->usuario->name ?? 'N/A',
                'barbero' => $venta->barbero->usuario->name ?? 'N/A',
                'monto_total' => (float)$venta->monto_total,
                'total_pagado' => (float)$totalPagado,
                'restante' => (float)($venta->monto_total - $totalPagado),
                'estado_pago' => $venta->estado_pago,
                'fecha' => $venta->created_at->format('d/m/Y H:i'),
                'detalles' => $venta->detalles->map(fn($d) => [
                    'nombre' => $d->producto ? $d->producto->nombre : $d->servicio->nombre,
                    'tipo' => $d->producto ? 'Producto' : 'Servicio',
                    'cantidad' => $d->cantidad,
                    'precio_unitario' => (float)$d->precio_unitario,
                    'subtotal' => (float)$d->subtotal,
                ]),
                'pagos' => $venta->detallePagos->map(fn($p) => [
                    'metodo' => $p->tipoPago->nombre,
                    'monto' => (float)$p->monto,
                    'fecha' => $p->created_at->format('d/m/Y H:i'),
                ]),
            ],
            'tiposPago' => TipoPago::all(['id', 'nombre']),
        ]);
    }

    /**
     * Add a payment to an existing sale.
     */
    public function addPayment(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'tipo_pago_id' => 'required|exists:tipo_pagos,id',
            'monto' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            DetallePago::create([
                'venta_id' => $venta->id,
                'tipo_pago_id' => $validated['tipo_pago_id'],
                'monto' => $validated['monto'],
            ]);

            // Recalcular estado
            $totalPagado = $venta->detallePagos()->sum('monto');
            $estadoPago = 'parcial';
            if ($totalPagado >= $venta->monto_total) {
                $estadoPago = 'completado';
            }

            $venta->update(['estado_pago' => $estadoPago]);

            DB::commit();

            return redirect()->back()->with('success', 'Pago registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        try {
            DB::beginTransaction();
            
            // Revertir stock
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto_id) {
                    $detalle->producto->increment('stock_actual', $detalle->cantidad);
                }
            }
            
            $venta->delete(); // Gracias a las FK onDelete cascade si están configuradas, sino hay que borrar manual
            
            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta eliminada y stock restaurado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la venta.');
        }
    }

    /**
     * Verifica si un barbero está disponible en una fecha y hora específica
     * según sus horarios regulares y excepciones
     */
    private function esBarberoDisponibleEnHorario($barberoId, Carbon $fechaHora)
    {
        // Si el barbero no tiene horarios definidos, asumimos disponibilidad 24/7
        $tieneHorarios = HorarioBarbero::where('barbero_id', $barberoId)->exists();
        
        if (!$tieneHorarios) {
            return true;
        }

        $fecha = $fechaHora->format('Y-m-d');
        $hora = $fechaHora->format('H:i:s');
        $diaSemana = $fechaHora->dayOfWeek; // 0 (domingo) a 6 (sábado)
        
        // Ajustar diaSemana si en la base de datos es 1-7 (lunes-domingo)
        // Carbon: 0=dom, 1=lun...
        // Si en BD es 1=lun, 2=mar..., 7=dom
        $diaSemana = ($diaSemana == 0) ? 7 : $diaSemana;

        // 1. Verificar excepciones (incluye días no laborables específicos)
        $excepcion = ExcepcionHorario::where('barbero_id', $barberoId)
            ->where('fecha', $fecha)
            ->first();

        if ($excepcion) {
            if (!$excepcion->es_laborable) {
                return false;
            }
            // Si hay horario especial en la excepción
            if ($excepcion->hora_inicio && $excepcion->hora_fin) {
                return $hora >= $excepcion->hora_inicio && $hora <= $excepcion->hora_fin;
            }
            return true;
        }

        // 2. Verificar el horario regular del día de la semana
        $horarioRegular = HorarioBarbero::where('barbero_id', $barberoId)
            ->where('dia_semana', (string)$diaSemana)
            ->first();

        if (!$horarioRegular) {
            return false;
        }

        return $hora >= $horarioRegular->hora_inicio && $hora <= $horarioRegular->hora_fin;
    }
}
