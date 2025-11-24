<?php

namespace App\Http\Controllers;

use App\Models\Barbero;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Configuracion;
use App\Models\Servicio;
use App\Models\TipoPago;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $configuracion = Configuracion::where('nombre', 'Porcentaje de Reserva de Citas')
            ->first();
        $porcentajeReserva = $configuracion ? (float) $configuracion->valor : 0;
        return Inertia::render('Cita/CreateCitaCliente',[
            'porcentajeReserva' => $porcentajeReserva,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'barbero_id' => 'nullable|exists:barberos,id',
            'servicios' => 'required|array|min:1',
            'servicios.*' => 'exists:servicios,id',
            'monto_total' => 'required|numeric|min:0',
            'pago_inicial' => 'required|numeric|min:0',
            'duracion_total' => 'required|integer|min:1',
        ]);
        $usuarioAutenticado = Auth::user();
        $clienteAutenticado = $usuarioAutenticado->cliente;
        
        $configuracion = Configuracion::where('nombre', 'Porcentaje de Reserva de Citas')
            ->first();
        $tipoPagoQr = TipoPago::where('nombre','QR')->first();
        $valorPorcentajeReserva = $configuracion ? (float) $configuracion->valor : 0;
        $porcentajeReserva = $valorPorcentajeReserva/100;
        $montoReservaCalculado = $porcentajeReserva  * $validated['monto_total'];
        try {
            DB::beginTransaction();

            // Crear fecha y hora combinadas
            $fechaHora = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);

            // Verificar que el barbero esté disponible en esa fecha/hora
            $barberoId = $validated['barbero_id'];
            if (!$barberoId) {
                $barberoDisponible = Barbero::where('estado_barbero', 'disponible')
                    ->whereDoesntHave('citas', function ($query) use ($fechaHora) {
                        $query->where('fecha', $fechaHora)
                              ->where('estado', '!=', 'cancelada');
                    })
                    ->first();

                if (!$barberoDisponible) {
                    return redirect()->back()
                        ->with('error', 'No hay barberos disponibles en ese horario')
                        ->withInput();
                }

                $barberoId = $barberoDisponible->id;
            }

            //si el barbero ya tiene una cita a esa hora
            $citaExistente = Cita::where('barbero_id', $barberoId)
                ->where('fecha', $fechaHora)
                ->whereIn('estado', ['pendiente', 'confirmada']) 
                ->exists();

            if ($citaExistente) {
                return redirect()->back()
                    ->with('error', 'El barbero ya tiene una cita en ese horario')
                    ->withInput();
            }

            $citaExistente = Cita::where('cliente_id', $clienteAutenticado->id)
                ->where('fecha', $fechaHora)
                ->whereIn('estado', ['pendiente', 'confirmada']) 
                ->exists();
            if($citaExistente){
                return redirect()->back()
                    ->with('error', 'Ya tienes una cita agendada en ese horario')
                    ->withInput();
            }

            // Crear la cita
            $cita = Cita::create([
                'cliente_id' => $clienteAutenticado->id,
                'barbero_id' => $validated['barbero_id'],
                'fecha' => $fechaHora,
                'estado' => 'pendiente',
                'monto_total' => $validated['monto_total'],
                'pago_inicial' => $montoReservaCalculado,
                'porcentaje_cita' => $porcentajeReserva,
                'tipo_pago_id'=> $tipoPagoQr->id,//por defecto
            ]);

            // Registrar los servicios de la cita
            foreach ($validated['servicios'] as $servicioId) {
                CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $servicioId,
                ]);
            }
            DB::commit();

            return redirect()->route('cita-cliente.create')
            ->with('success', '¡Cita registrada exitosamente! Tu cita ha sido agendada.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar cita', [
                'error' => $e->getMessage(),
                'user' => $clienteAutenticado->id,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Error al registrar la cita. Por favor intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cita $cita)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cita $cita)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cita $cita)
    {
        //
    }
    
    public function getBarberosDisponibles(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
        ]);
        $fecha = $validated['fecha'];
        $hora = $validated['hora'];
        $fechaHoraInicio = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
        
        $citasEnFechaHora = Cita::where('fecha', $fechaHoraInicio)
            ->whereIn('estado', ['pendiente', 'confirmada']) 
            ->with('barbero')
            ->get();
        // return response()->json([
        //     'citasEnFechaHora' => $citasEnFechaHora,
        //     "fechaHora" => $fechaHoraInicio,
        //     "fecha" => $validated['fecha'],
        //     "hora" => $validated['hora'],
        //     "fecha_hora" => $fecha . ' ' . $hora,
        // ]);
        $serviciosDisponibles = Servicio::where('estado', 'activo')
            ->whereHas('servicioBarberos.barbero', function($query) {
                $query->where('estado_barbero', 'disponible');
            })
            ->select('id','nombre','descripcion','precio','duracion_estimada')
            ->get()
            ->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'precio' => (float) $servicio->precio, 
                    'duracion_estimada' => $servicio->duracion_estimada,
                ];
            });
            
        $barberosDisponibles = collect();
        //si no hay cutas en determinada fecha y hora, entonces todos estan libres como tal
        if( $citasEnFechaHora->isEmpty() ){
            //la consulta va cambiar cuando se modifique el usuario (campo estado),
            $barberosDisponibles = Barbero::where('estado_barbero', 'disponible')
            ->with([
                // Cargar la relación 'usuario' para obtener el nombre
                'usuario:id,name', 
                // Cargar la relación 'servicioBarberos' y anidar la carga de 'servicio'
                'servicioBarberos.servicio' => function ($query) {
                    $query->where('estado', 'activo')
                          ->select('id', 'nombre', 'precio', 'duracion_estimada');
                }
            ])
            ->get();
        }else{
            //en el caso de que haya citas en esa fecha y hora, entonces solamente filtrame los barberos que no esten en esas citas
            // Obtener los IDs de los barberos que ya tienen citas en esa fecha y hora
            $barberosOcupadosIds = $citasEnFechaHora->pluck('barbero_id')->filter()->unique();
            // Obtener los barberos que están disponibles (no tienen citas en esa fecha y hora)
            $barberosDisponibles = Barbero::where('estado_barbero', 'disponible')
            ->whereNotIn('id', $barberosOcupadosIds)
            ->with([
                // Cargar la relación 'usuario' para obtener el nombre
                'usuario:id,name',
                // Cargar la relación 'servicioBarberos' y anidar la carga de 'servicio'
                'servicioBarberos.servicio' => function ($query) {
                    $query->where('estado', 'activo')
                          ->select('id', 'nombre', 'precio', 'duracion_estimada');
                }
            ])
            ->get();
        }
        $formattedBarberos = $barberosDisponibles->map(function ($barbero) {
            // Mapear los servicios del barbero
            $servicios = $barbero->servicioBarberos->map(function ($servicioBarbero) {
                
                if ($servicioBarbero->servicio) {
                    return [
                        'id' => $servicioBarbero->servicio->id,
                        'nombre' => $servicioBarbero->servicio->nombre,
                        'descripcion' => $servicioBarbero->servicio->descripcion,
                        'precio' => (float)$servicioBarbero->servicio->precio,
                        'duracion_estimada' => $servicioBarbero->servicio->duracion_estimada,
                    ];
                }
                return null;
            })->filter()->values(); 

            return [
                'id' => $barbero->id,
                'estado_barbero' => $barbero->estado_barbero,
                'name' => $barbero->usuario->name ?? 'N/A', // Usar el nombre del usuario relacionado
                'servicios' => $servicios,
            ];
        });
        return response()->json(
            [
                'barberos' => $formattedBarberos,
                'servicios' => $serviciosDisponibles,
            ]
        );
        
    }
}
