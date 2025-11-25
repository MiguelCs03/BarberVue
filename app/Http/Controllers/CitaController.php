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
    public function indexAdministrativo(){
        $citas = Cita::with([
            'cliente.usuario:id,name',
            'barbero.usuario:id,name',
        ])
        ->select('id', 'cliente_id', 'barbero_id', 'fecha','estado','pago_inicial','porcentaje_cita')
        ->orderBy('fecha', 'desc')
        ->paginate(10);

        $citas->getCollection()->transform(function ($cita) {
            return [
                'id' => $cita->id,
                'fecha' => $cita->fecha ? Carbon::parse($cita->fecha)->format('d/m/Y H:i') : null,
                'estado' => $cita->estado,
                'pago_inicial' => (float) $cita->pago_inicial,
                'porcentaje_cita' => (float) $cita->porcentaje_cita,
                'cliente' => $cita->cliente && $cita->cliente->usuario ? $cita->cliente->usuario->name : null,
                'barbero' => $cita->barbero && $cita->barbero->usuario ? $cita->barbero->usuario->name : null,
            ];
        });
        
        return Inertia::render('Cita/administrador/Index',[
            'citas' => $citas,
        ]);
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
        return Inertia::render('Cita/cliente/CreateCita',[
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
            if($barberoId){
                $citaExistenteConOtroBarbero = Cita::where('cliente_id', $clienteAutenticado->id)
                        ->where('barbero_id','!=',$barberoId)
                        ->where('fecha', $fechaHora)
                        ->whereIn('estado', ['pendiente']) 
                        ->exists();
                if($citaExistenteConOtroBarbero){
                    return redirect()->back()
                        ->with('error', 'Ya tienes una cita agendada en ese horario con otro barbero')                       ->withInput();
                }
            }

            //si barbero_id es nulo
            if (!$barberoId) {
                $totalBarberos = Barbero::where('estado_barbero', 'disponible')
                ->whereHas('servicioBarberos.servicio', function ($query) {
                    $query->where('estado', 'activo');
                })->count();

                // $citasPendientesSinBarberoAsignado = Cita::where('fecha', $fechaHora) 
                // ->whereNull('barbero_id')
                // ->whereIn('estado', ['pendiente'])
                // ->count(); 
            
                
                // $citasPendientesDistinct = Cita::where('fecha', $fechaHora) 
                // ->whereNotNull('barbero_id')
                // ->whereIn('estado', ['pendiente'])
                // ->distinct()
                // ->count('barbero_id');
                $citasEnFechaHora = Cita::where('fecha', $fechaHora)
                ->whereIn('estado', ['pendiente']) 
                ->with('barbero')
                ->get()
                ->unique('barbero_id');
                //$totalCitasPendientes = $citasPendientesSinBarberoAsignado + $citasPendientesDistinct;
                $totalCitasPendientes = $citasEnFechaHora->count();
                if (($totalCitasPendientes + 1) > $totalBarberos) {
                    return back()
                        ->with('error', 'Todos los barberos están ocupados en ese horario.');
                        //->withInput();
                }

                //podriamos assignar a un barbero aleatoriamente disponible
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
                'tipo_pago_id'=> $tipoPagoQr->id,
            ]);

            // Registrar los servicios de la cita
            foreach ($validated['servicios'] as $servicioId) {
                CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $servicioId,
                ]);
            }
            DB::commit();

            return redirect()->route('citas-cliente.index')
            ->with('success', '¡Cita registrada exitosamente! Tu cita ha sido agendada.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar cita', [
                'error' => $e->getMessage(),
                'user' => $clienteAutenticado->id,
                'data' => $validated
            ]);

            return back()
                ->with('error', 'Error al registrar la cita. Por favor intenta nuevamente.')
                ->withInput();
        }
    }

    public function indexCliente(){
        $usuarioAutenticado = Auth::user();
        $clienteAutenticado = $usuarioAutenticado->cliente;
        $citas = Cita::where('cliente_id',$clienteAutenticado->id)->with([
            'barbero.usuario:id,name',
        ])
        ->select('id', 'barbero_id', 'fecha','estado','pago_inicial')
        ->orderBy('fecha', 'asc')
        ->paginate(10);

        $citas->getCollection()->transform(function ($cita) {
            return [
                'id' => $cita->id,
                'fecha' => $cita->fecha ? Carbon::parse($cita->fecha)->format('d/m/Y H:i') : null,
                'estado' => $cita->estado,
                'pago_inicial' => (float) $cita->pago_inicial,
                'barbero' => $cita->barbero && $cita->barbero->usuario ? $cita->barbero->usuario->name : null,
            ];
        });
        
        return Inertia::render('Cita/cliente/Index',[
            'citas' => $citas,
        ]);
    }

    public function showCliente(string $id){
        $cita = Cita::with([
            'barbero.usuario',
            'citaServicios.servicio',
            'tipoPago'
        ])->findOrFail($id);

        $citaFormateada = [
            'id' => $cita->id,
            'fecha' => $cita->fecha ? Carbon::parse($cita->fecha)->format('d/m/Y H:i') : null,
            'estado' => $cita->estado,
            'pago_inicial' => (float) $cita->pago_inicial,
            'monto_total' => (float) $cita->monto_total,
            'barbero' => [
                'id' => $cita->barbero?->id,
                'nombre' => $cita->barbero->usuario->name ?? null,
            ],
            'servicios' => $cita->citaServicios->map(function($citaServicio) {
                return [
                    'id' => $citaServicio->servicio->id ?? null,
                    'nombre' => $citaServicio->servicio->nombre ?? null,
                    'precio' => (float) $citaServicio->servicio->precio ?? 0,
                ];
            }),
            'tipo_pago' => [
                'id' => $cita->tipoPago->id ?? null,
                'nombre' => $cita->tipoPago->nombre ?? null,
            ],
            'created_at' => Carbon::parse($cita->created_at)->format('d/m/Y H:i'),
            'updated_at' => Carbon::parse($cita->updated_at)->format('d/m/Y H:i'),
        ];

        return Inertia::render('Cita/cliente/Show', [
            'cita' => $citaFormateada
        ]);
    }

    public function cancelarCita(string $id){
        try {
            $cita = Cita::findOrFail($id);

            // Opcional: Validar que la cita pertenezca al usuario actual si es cliente
            //$user = Auth::user();
            //si tuvieramos spatie
            // if ($user->hasRole('cliente') && $user->cliente && $cita->cliente_id !== $user->cliente->id) {
            //     return redirect()->back()->with('error', 'No tienes permiso para cancelar esta cita.');
            // }

            // Validar estado actual
            if ($cita->estado === 'completada') {
                return redirect()->back()->with('error', 'No se puede cancelar una cita que ya ha sido completada.');
            }

            $cita->estado = 'cancelada';
            $cita->save();

            return redirect()->back()->with('success', 'Cita cancelada exitosamente.');

        } catch (Exception $e) {
            Log::error('Error al cancelar cita: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al intentar cancelar la cita.');
        }
    }
    /**
     * Display the specified resource.
     */
    public function showAdministrativo(string $id)
    {
        $cita = Cita::with([
            'cliente.usuario',
            'barbero.usuario',
            'citaServicios.servicio',
            'tipoPago'
        ])->findOrFail($id);

         $citaFormateada = $this->obtenerCitaFormateada($cita);

        return Inertia::render('Cita/administrador/Show', [
            'cita' => $citaFormateada
        ]);
    }
    public function obtenerCitaFormateada(Cita $cita){
        return [
            'id' => $cita->id,
            'fecha' => $cita->fecha ? Carbon::parse($cita->fecha)->format('d/m/Y H:i') : null,
            'estado' => $cita->estado,
            'pago_inicial' => (float) $cita->pago_inicial,
            'porcentaje_cita' => (float) $cita->porcentaje_cita,
            'monto_total' => (float) $cita->monto_total,
            'cliente' => [
                'id' => $cita->cliente?->id,
                'nombre' => $cita->cliente->usuario->name ?? null,
            ],
            'barbero' => [
                'id' => $cita->barbero?->id,
                'nombre' => $cita->barbero->usuario->name ?? null,
            ],
            'servicios' => $cita->citaServicios->map(function($citaServicio) {
                return [
                    'id' => $citaServicio->servicio->id ?? null,
                    'nombre' => $citaServicio->servicio->nombre ?? null,
                    'precio' => (float) $citaServicio->servicio->precio ?? 0,
                ];
            }),
            'tipo_pago' => [
                'id' => $cita->tipoPago->id ?? null,
                'nombre' => $cita->tipoPago->nombre ?? null,
            ],
            'created_at' => Carbon::parse($cita->created_at)->format('d/m/Y H:i'),
            'updated_at' => Carbon::parse($cita->updated_at)->format('d/m/Y H:i'),
        ];

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
    //metodo para clientes
    public function getBarberosDisponibles(Request $request)
    {
        $usuarioAutenticado = Auth::user();
        // dd($usuarioAutenticado);
        // dd($request->all());
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
        ]);
        
        $fechaHoraInicio = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
        
        $citasEnFechaHora = Cita::where('fecha', $fechaHoraInicio)
            ->whereIn('estado', ['pendiente']) 
            ->with('barbero')
            ->get()
            ->unique('barbero_id');
      
        $clienteAutenticado = $usuarioAutenticado->cliente;
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
            ->whereHas('servicioBarberos.servicio', function ($query) {
                $query->where('estado', 'activo');
            })
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
        
            $totalBarberos = Barbero::where('estado_barbero', 'disponible')
                ->whereHas('servicioBarberos.servicio', function ($query) {
                    $query->where('estado', 'activo');
                })
                ->count();
                
            if($totalBarberos == $citasEnFechaHora->count()){
                $barberosDisponibles = collect();
            }else{
                //en el caso de que haya citas en esa fecha y hora, entonces solamente filtrame los barberos que no esten en esas citas
                // Obtener los IDs de los barberos que ya tienen citas en esa fecha y hora
                $barberosOcupadosIds = $citasEnFechaHora->pluck('barbero_id')->filter()->unique();
                // Obtener los barberos que están disponibles (no tienen citas en esa fecha y hora)
                $barberosDisponibles = Barbero::where('estado_barbero', 'disponible')
                ->whereNotIn('id', $barberosOcupadosIds)
                ->whereHas('servicioBarberos.servicio', function ($query) {
                    $query->where('estado', 'activo');
                })
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
            
            #para agregar los barberos a la caja
            $barberosDeMiCitaIds = Cita::where('fecha', $fechaHoraInicio)
                    ->whereIn('estado', ['pendiente'])
                    ->whereNotNull('barbero_id')
                    ->where('cliente_id', $clienteAutenticado->id) 
                    ->pluck('barbero_id')
                    ->unique();
            if ($barberosDeMiCitaIds->isNotEmpty()) {
                // IDs que ya están en barberosDisponibles
                $idsYaIncluidos = $barberosDisponibles->pluck('id');
                
                
                $idsParaAgregar = $barberosDeMiCitaIds->diff($idsYaIncluidos);
            
                if ($idsParaAgregar->isNotEmpty()) {
                    // Traer los barberos faltantes
                    $barberosFaltantes = Barbero::whereIn('id', $idsParaAgregar)
                        ->where('estado_barbero', 'disponible')
                        ->with([
                            'usuario:id,name',
                            'servicioBarberos.servicio' => function ($query) {
                                $query->where('estado', 'activo')
                                    ->select('id', 'nombre', 'descripcion', 'precio', 'duracion_estimada');
                            }
                        ])
                        ->get();
                    
                    $barberosDisponibles = $barberosDisponibles->merge($barberosFaltantes);
                }
            } 
            
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
