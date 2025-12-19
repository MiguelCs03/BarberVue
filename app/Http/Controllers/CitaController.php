<?php

namespace App\Http\Controllers;

use App\Services\PagoFacilService;

use App\Models\Barbero;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\ExcepcionHorario;
use App\Models\HorarioBarbero;
use App\Models\Servicio;
use App\Models\TipoPago;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        $configuracion = Configuracion::where('nombre', 'porcentaje_cita')
            ->first();
        $porcentajeReserva = $configuracion ? (float) $configuracion->valor : 0;
        return Inertia::render('Cita/cliente/CreateCita',[
            'porcentajeReserva' => $porcentajeReserva,
        ]);
    }

    public function createAdmin(){
        $configuracion = Configuracion::where('nombre', 'porcentaje_cita')
            ->first();
        $porcentajeReserva = $configuracion ? (float) $configuracion->valor : 0;
        return Inertia::render('Cita/administrador/CreateCitaAdmin',[
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
        $rolDeUsuario = $usuarioAutenticado->rol;
        $configuracion = Configuracion::where('nombre', 'porcentaje_cita')
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
                // Validar que el barbero esté disponible según su horario
                if (!$this->esBarberoDisponibleEnHorario($barberoId, $fechaHora)) {
                    return redirect()->back()
                        ->with('error', 'El barbero seleccionado no está disponible en ese horario.')
                        ->withInput();
                }
                #podria considerarse no validar esto
                $citaExistenteConOtroBarbero = Cita::where('cliente_id', $clienteAutenticado->id)
                        ->where('barbero_id','!=',$barberoId)
                        ->where('fecha', $fechaHora)
                        ->whereIn('estado', ['pendiente']) 
                        ->exists();
                if($citaExistenteConOtroBarbero){
                    return redirect()->back()
                        ->with('error', 'Ya tienes una cita agendada en ese horario con otro barbero')                       
                        ->withInput();
                }
            }

            // Generar UUID para la transacción
            $uuid = (string) Str::uuid();

            // Generar QR con PagoFácil
            $pagoFacilService = new PagoFacilService();
            
            $clientData = [
                'name' => $usuarioAutenticado->name,
                'documentId' => '12345678', // Valor por defecto (el cliente no tiene CI en la BD)
                'phoneNumber' => '70000000', // Valor por defecto (el cliente no tiene teléfono en la BD)
                'email' => $usuarioAutenticado->email ?? '',
            ];

            $qrData = $pagoFacilService->generarQr(
                $uuid,
                $montoReservaCalculado,
                $clientData,
                'Reserva de Cita - BarberVue'
            );

            // Crear la cita con los datos de PagoFácil
            $cita = Cita::create([
                'cliente_id' => $clienteAutenticado->id,
                'barbero_id' => $validated['barbero_id'],
                'fecha' => $fechaHora,
                'estado' => 'pendiente',
                'monto_total' => $validated['monto_total'],
                'pago_inicial' => $montoReservaCalculado,
                'porcentaje_cita' => $porcentajeReserva,
                'tipo_pago_id'=> $tipoPagoQr->id,
                'transaccion_uuid' => $uuid,
                'transaccion_id_pagofacil' => $qrData['transactionId'],
                'qr_image' => $qrData['qrImage'],
            ]);

            // Registrar los servicios de la cita
            foreach ($validated['servicios'] as $servicioId) {
                CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $servicioId,
                ]);
            }
            DB::commit();

            // Renderizar vista de pago con el QR
            return Inertia::render('Cita/Payment', [
                'cita' => [
                    'id' => $cita->id,
                    'qrImage' => $qrData['qrImage'],
                    'monto' => $montoReservaCalculado,
                    'fecha' => $fechaHora->format('d/m/Y H:i'),
                ],
            ]);

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

    //TODO se puede refactorizar con el store de cliente
    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id', 
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'barbero_id' => 'nullable|exists:barberos,id',
            'servicios' => 'required|array|min:1',
            'servicios.*' => 'exists:servicios,id',
            'monto_total' => 'required|numeric|min:0',
            'pago_inicial' => 'required|numeric|min:0',
            'duracion_total' => 'required|integer|min:1',
        ]);

        if (!$validated['cliente_id']) {
            return redirect()->back()
                ->with('error', 'Debes seleccionar un cliente para crear la cita.')
                ->withInput();
        }

        
        
        // Obtener el cliente seleccionado (no el autenticado)
        $cliente = Cliente::find($validated['cliente_id']);
        
        if (!$cliente) {
            return redirect()->back()
                ->with('error', 'El cliente seleccionado no existe.')
                ->withInput();
        }

        $configuracion = Configuracion::where('nombre', 'porcentaje_cita')
            ->first();
        $tipoPagoQr = TipoPago::where('nombre', 'QR')->first();
        $valorPorcentajeReserva = $configuracion ? (float) $configuracion->valor : 0;
        $porcentajeReserva = $valorPorcentajeReserva / 100;
        $montoReservaCalculado = $porcentajeReserva * $validated['monto_total'];

        try {
            DB::beginTransaction();

            // Crear fecha y hora combinadas
            $fechaHora = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);

            // Verificar que el barbero esté disponible en esa fecha/hora
            $barberoId = $validated['barbero_id'];
            if ($barberoId) {
                // Validar que el barbero esté disponible según su horario
                if (!$this->esBarberoDisponibleEnHorario($barberoId, $fechaHora)) {
                    return redirect()->back()
                        ->with('error', 'El barbero seleccionado no está disponible en ese horario.')
                        ->withInput();
                }

                // Validar que el cliente no tenga otra cita en ese horario con otro barbero
                $citaExistenteConOtroBarbero = Cita::where('cliente_id', $cliente->id)
                    ->where('barbero_id', '!=', $barberoId)
                    ->where('fecha', $fechaHora)
                    ->whereIn('estado', ['pendiente'])
                    ->exists();
                
                if ($citaExistenteConOtroBarbero) {
                    return redirect()->back()
                        ->with('error', 'El cliente ya tiene una cita agendada en ese horario con otro barbero')
                        ->withInput();
                }
            }

            // Generar UUID para la transacción
            $uuid = (string) Str::uuid();

            // Generar QR con PagoFácil
            $pagoFacilService = new PagoFacilService();
            
            $clientData = [
                'name' => $cliente->usuario->name,
                'documentId' => '12345678', // Valor por defecto (el cliente no tiene CI en la BD)
                'phoneNumber' => '70000000', // Valor por defecto (el cliente no tiene teléfono en la BD)
                'email' => $cliente->usuario->email ?? '',
            ];

            $qrData = $pagoFacilService->generarQr(
                $uuid,
                $montoReservaCalculado,
                $clientData,
                'Reserva de Cita - BarberVue'
            );

            // Crear la cita con los datos de PagoFácil
            $cita = Cita::create([
                'cliente_id' => $cliente->id, 
                'barbero_id' => $validated['barbero_id'],
                'fecha' => $fechaHora,
                'estado' => 'pendiente',
                'monto_total' => $validated['monto_total'],
                'pago_inicial' => $montoReservaCalculado,
                'porcentaje_cita' => $porcentajeReserva,
                'tipo_pago_id' => $tipoPagoQr->id,
                'transaccion_uuid' => $uuid,
                'transaccion_id_pagofacil' => $qrData['transactionId'],
                'qr_image' => $qrData['qrImage'],
            ]);

            // Registrar los servicios de la cita
            foreach ($validated['servicios'] as $servicioId) {
                CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $servicioId,
                ]);
            }

            DB::commit();

            // Renderizar vista de pago con el QR
            return Inertia::render('Cita/Payment', [
                'cita' => [
                    'id' => $cita->id,
                    'qrImage' => $qrData['qrImage'],
                    'monto' => $montoReservaCalculado,
                    'fecha' => $fechaHora->format('d/m/Y H:i'),
                    'cliente' => $cliente->usuario->name,
                ],
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al registrar cita desde admin', [
                'error' => $e->getMessage(),
               'cliente_id' => $validated['cliente_id'],
                'data' => $validated
            ]);

            return redirect()->back()
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
        ->orderBy('fecha', 'desc')
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
            if ($cita->estado !== 'pendiente') {
                return redirect()->back()
                ->with('error', 'No se puede cancelar una cita que ya ha sido completada.')
                ->withInput();
            }

            $cita->estado = 'cancelada';
            $cita->save();

            return redirect()
            ->back()
            ->with('success', 'Cita cancelada exitosamente.')
            ->withInput();

        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error al cancelar cita: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar cancelar la cita.')
                ->withInput();

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
       
    
    }

    public function editCliente(string $id){
        
    
        $cita = Cita::with([
            'barbero.usuario',
            'citaServicios.servicio'
        ])->findOrFail($id);

        $citaFormateada = [
            'id' => $cita->id,
            'fecha' => Carbon::parse($cita->fecha)->format('Y-m-d'),
            'hora' => Carbon::parse($cita->fecha)->format('H:i'),
            'barbero' => $cita->barbero ? [
                'id' => $cita->barbero->id,
                'nombre' => $cita->barbero->usuario->name ?? 'No asignado',
            ] : null,
            //'servicios_ids' => $cita->citaServicios->pluck('servicio_id')->toArray(),
            'servicios' => $cita->citaServicios->map(function($citaServicio) {
                return [
                    'id' => $citaServicio->servicio->id,
                    'nombre' => $citaServicio->servicio->nombre,
                    'descripcion' => $citaServicio->servicio->descripcion,
                    'precio' => (float) $citaServicio->servicio->precio,
                    'duracion_estimada' => $citaServicio->servicio->duracion_estimada,
                ];
            })->toArray(),
        ];

        return Inertia::render('Cita/cliente/EditCita', [
            'cita' => $citaFormateada,
        ]);
    
    }



    public function editAdmin(string $id){
        
    
        $cita = Cita::with([
            'barbero.usuario',
            'cliente.usuario',
            'citaServicios.servicio'
        ])->findOrFail($id);

        $citaFormateada = [
            'id' => $cita->id,
            'fecha' => Carbon::parse($cita->fecha)->format('Y-m-d'),
            'hora' => Carbon::parse($cita->fecha)->format('H:i'),
            'estado' => $cita->estado,
            'barbero' => $cita->barbero ? [
                'id' => $cita->barbero->id,
                'nombre' => $cita->barbero->usuario->name ?? 'No asignado',
            ] : null,
            'cliente' => $cita->cliente ? [
                'id' => $cita->cliente->id,
                'nombre' => $cita->cliente->usuario->name ?? 'No asignado',
            ] : null,
            //'servicios_ids' => $cita->citaServicios->pluck('servicio_id')->toArray(),
            'servicios' => $cita->citaServicios->map(function($citaServicio) {
                return [
                    'id' => $citaServicio->servicio->id,
                    'nombre' => $citaServicio->servicio->nombre,
                    'descripcion' => $citaServicio->servicio->descripcion,
                    'precio' => (float) $citaServicio->servicio->precio,
                    'duracion_estimada' => $citaServicio->servicio->duracion_estimada,
                ];
            })->toArray(),
        ];

        return Inertia::render('Cita/administrador/EditarCita', [
            'cita' => $citaFormateada,
        ]);
    
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function updateCliente(Request $request, $id)
    {
        // 1. Validar datos de entrada
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'barbero_id' => 'required|exists:barberos,id',
        ]);
        $usuarioAutenticado = Auth::user();
        $rolUsuario = $usuarioAutenticado->rol;
        $clienteAutenticado = $usuarioAutenticado->cliente;
        // 2. Buscar la cita y verificar propiedad
        try{

            DB::beginTransaction();
            $cita = Cita::findOrFail($id);
    
            // Seguridad: Verificar que la cita sea del cliente autenticado
            if ($cita->cliente_id !== $clienteAutenticado->id) {
                #excepcion
                return redirect()->back()
                            ->with('error', 'usuario no autorizado para editar esta cita.')                       
                            ->withInput();
            }
    
            // Seguridad: Solo permitir editar citas pendientes
            if ($cita->estado !== 'pendiente') {
                #excepcion
                return redirect()->back()
                            ->with('error', 'Solo se pueden editar citas en estado pendiente.')                       
                            ->withInput();
            }
    
            $nuevaFechaHora = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
            // 3. Validar que el barbero esté disponible en el nuevo horario
            $citaConflicto = Cita::where('fecha', $nuevaFechaHora)
                ->where('barbero_id', $validated['barbero_id'])
                ->where('id', '!=', $id) 
                ->whereIn('estado', ['pendiente']) // Estados que ocupan lugar
                ->exists();
    
            if ($citaConflicto) {
                return redirect()->back()
                    ->with('error', 'El barbero seleccionado ya no está disponible en el nuevo horario.')
                    ->withInput();
            }
    
            $barbero = Barbero::find($validated['barbero_id']);
            if ($barbero->estado_barbero !== 'disponible') {
                return redirect()->back()
                    ->with('error', 'El barbero seleccionado ya no está disponible actualmente.')
                    ->withInput();
            }
    
            
            $cita->update([
                'fecha' => $nuevaFechaHora, 
                'barbero_id' => $validated['barbero_id'],
            ]);
            DB::commit();
            if($rolUsuario == 'cliente'){
                return to_route('citas-cliente.show', $cita->id)
                    ->with('success', 'La cita ha sido actualizada correctamente.');
            }else{
                return to_route('citas-admin.show', $cita->id)
                ->with('success', 'La cita ha sido actualizada correctamente.');
            }
        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error al actualizar cita: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar actualizar la cita.')
                ->withInput();

        }
    }


    public function updateAdmin(Request $request, $id)
    {
        // 1. Validar datos de entrada
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'barbero_id' => 'required|exists:barberos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'estado' => 'required|in:pendiente,cancelada,completada,cancelada',
        ]);


        try {
            DB::beginTransaction();

            $cita = Cita::findOrFail($id);


            $nuevaFechaHora = Carbon::parse("{$validated['fecha']} {$validated['hora']}");

            // 3. Validar que el barbero esté disponible en el nuevo horario
            // Solo si se está cambiando fecha, hora o barbero
            $cambioHorarioOBarbero = (
                $cita->fecha != $nuevaFechaHora ||
                $cita->barbero_id != $validated['barbero_id']
            );

            if ($cambioHorarioOBarbero) {
                $citaConflicto = Cita::where('fecha', $nuevaFechaHora)
                    ->where('barbero_id', $validated['barbero_id'])
                    ->where('id', '!=', $id)
                    ->whereIn('estado', ['pendiente', 'confirmada']) // Estados que ocupan lugar
                    ->exists();

                if ($citaConflicto) {
                    return redirect()->back()
                        ->with('error', 'El barbero seleccionado ya tiene una cita en ese horario.')
                        ->withInput();
                }

                $barbero = Barbero::find($validated['barbero_id']);
                if ($barbero->estado_barbero !== 'disponible') {
                    return redirect()->back()
                        ->with('error', 'El barbero seleccionado no está disponible actualmente.')
                        ->withInput();
                }
            }

            // Actualizar la cita
            $cita->update([
                'fecha' => $nuevaFechaHora,
                'barbero_id' => $validated['barbero_id'],
                'cliente_id' => $validated['cliente_id'],
                'estado' => $validated['estado'],
            ]);

            DB::commit();

            return to_route('citas-admin.show', $cita->id)
                ->with('success', 'La cita ha sido actualizada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar cita desde admin: ' . $e->getMessage(), [
                'cita_id' => $id,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar actualizar la cita.')
                ->withInput();
        }
    }
   
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cita $cita)
    {
        //
    }
    public function getBarberosDisponiblesAdmin(Request $request){
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
        ]);
        
        $cliente = $validated['cliente_id'];
        $cliente = $cliente ? Cliente::find($cliente) : null;
        $barberosDisponibles = $this->procesarBarberoDisponibles(
            $validated['fecha'],
            $validated['hora'],
            $cliente
        );
        
        return $barberosDisponibles;
    }
    //para usuario cliente
    public function getBarberosDisponiblesV2(Request $request){
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
        ]);
        
        $usuarioAutenticado = Auth::user();
        $clienteAutenticado = $usuarioAutenticado->cliente;
        
        $barberosDisponibles = $this->procesarBarberoDisponibles(
            $validated['fecha'],
            $validated['hora'],
            $clienteAutenticado
        );
        
        return $barberosDisponibles;
    }
    private function procesarBarberoDisponibles(string $fecha, string $hora, ?Cliente $clienteAuth)
    {   
        // $validated = $request->validate([
        //     'fecha' => 'required|date',
        //     'hora' => 'required|date_format:H:i',
        // ]);
        
        #$fechaHoraInicio = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
        $fechaHoraInicio = Carbon::parse("{$fecha} {$hora}");
        #$usuarioAutenticado = Auth::user();
        $citasEnFechaHora = Cita::where('fecha', $fechaHoraInicio)
            ->whereNotNull('barbero_id') 
            ->whereIn('estado', ['pendiente']) 
            ->with('barbero')
            ->get()
            ->unique('barbero_id');

        if($clienteAuth !== null){
            $clienteAutenticado = $clienteAuth;
            $misCitasEnEsaHora = $citasEnFechaHora->where('cliente_id', $clienteAutenticado->id);
            $misBarberos = $misCitasEnEsaHora->pluck('barbero')->unique('id');
        }
            

        $barberosOcupadosIds = $citasEnFechaHora->pluck('barbero_id')->filter()->unique();
        
        $barberosDisponibles = Barbero::where('estado_barbero', 'disponible')
            ->whereNotIn('id', $barberosOcupadosIds)
            ->whereHas('servicioBarberos.servicio', function ($query) {
                $query->where('estado', 'activo');
            })
            ->with([
                'usuario:id,name', 
                'servicioBarberos.servicio' => function ($query) {
                    $query->where('estado', 'activo')
                          ->select('id', 'nombre', 'descripcion', 'precio', 'duracion_estimada');
                }
            ])
            ->get();

        if($clienteAuth !== null){
            foreach ($misBarberos as $miBarbero) {
                
                if ($miBarbero && !$barberosDisponibles->contains('id', $miBarbero->id)) {
                    
                    $miBarbero->load([
                        'usuario:id,name',
                        'servicioBarberos.servicio' => function ($query) {
                            $query->where('estado', 'activo')
                                  ->select('id', 'nombre', 'descripcion', 'precio', 'duracion_estimada');
                        }
                    ]);
        
                    $barberosDisponibles->push($miBarbero);
                }
            }
        }
        // Agregar mis barberos a la colección de disponibles
        $formattedBarberos = $barberosDisponibles->map(function ($barbero) {
        
            // Mapeamos los servicios para quitar la estructura pivote y dejarlo limpio
            $servicios = $barbero->servicioBarberos->map(function ($sb) {
                return $sb->servicio ? [
                    'id' => $sb->servicio->id,
                    'nombre' => $sb->servicio->nombre,
                    'descripcion' => $sb->servicio->descripcion,
                    'precio' => (float) $sb->servicio->precio,
                    'duracion_estimada' => $sb->servicio->duracion_estimada,
                ] : null;
            })->filter()->values();
    
            return [
                'id' => $barbero->id,
                'estado_barbero' => $barbero->estado_barbero,
                'name' => $barbero->usuario->name ?? 'N/A', // Usar el nombre del usuario relacionado
                'servicios' => $servicios,
            ];
        })->values();
       
        return response()->json([
            'barberos' => $formattedBarberos,
        ]);
    }
    public function getBarberosDisponiblesParaEdicion(Request $request){
        // $usuarioAutenticado = Auth::user();
        // $clienteAutenticado = $usuarioAutenticado->cliente;
        $barberosDisponibles = $this->procesarBarberoDisponiblesParaEdicion($request);
        return $barberosDisponibles;
    }
    private function procesarBarberoDisponiblesParaEdicion(Request $request)
    {   
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'cita_id' => 'required|exists:citas,id',
        ]);
        // dd($validated);
        // Obtener los servicios de la cita actual
        $serviciosDeLaCita = CitaServicio::where('cita_id', $validated['cita_id'])
            ->pluck('servicio_id')
            ->toArray();
        Log::info("Servicios de la cita ID {$validated['cita_id']}:", $serviciosDeLaCita);
        $fechaHoraInicio = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
        $citaEspecifica = Cita::with('barbero')->findOrFail($validated['cita_id']);
        $barberoDeCitaEspecifica = $citaEspecifica->barbero;//puede ser null
        $citasEnFechaHora = Cita::where('fecha', $fechaHoraInicio)
          #  ->where('id', '!=', $validated['cita_id'])
          #  ->where('barbero_id', '!=', $barberoDeCitaEspecifica?->id) // Excluir el barbero de la cita específica
            ->whereNotNull('barbero_id') // Excluir el barbero de la cita específica
            ->whereIn('estado', ['pendiente']) 
            ->with('barbero')
            ->get()
            ->unique('barbero_id');

        $barberosOcupadosIds = $citasEnFechaHora->pluck('barbero_id')->filter()->unique();
        #que tengan al menos los servicios de la cita
        $barberosDisponibles = Barbero::where('estado_barbero', 'disponible')
                ->whereNotIn('id', $barberosOcupadosIds)
                ->whereHas('servicioBarberos', function ($query) use ($serviciosDeLaCita) {
                    $query->whereIn('servicio_id', $serviciosDeLaCita)
                        ->whereHas('servicio', function ($q) {
                            $q->where('estado', 'activo');
                        });
                })
                ->with([
                    'usuario:id,name',
                    'serviciobarberos'
                ])
                ->get(); 
        #dd($barberosDisponibles->toArray());
        // Filtrar: Solo barberos que ofrezcan TODOS los servicios de la cita
        $barberosDisponiblesFiltrado = $barberosDisponibles->filter(function ($barbero) use ($serviciosDeLaCita) {
            // Obtener los servicios del barbero actual
            $serviciosDelBarbero = $barbero->servicioBarberos->pluck('servicio_id')->toArray();
            
            // Loguear qué tiene este barbero
            Log::info("Revisando Barbero ID: {$barbero->id} ({$barbero->usuario->name})");
            Log::info("Servicios del Barbero:", $serviciosDelBarbero);
        
            foreach ($serviciosDeLaCita as $servicioId) {
                // Verificar si existe (OJO: a veces los tipos de datos int vs string fallan)
                if (!in_array($servicioId, $serviciosDelBarbero)) {
                    Log::warning("RECHAZADO: Le falta el servicio ID: {$servicioId}");
                    return false;
                }
            }
        
            Log::info("ACEPTADO: Tiene todos los servicios.");
            return true;
        });

        if ($barberoDeCitaEspecifica) {
            // Verificamos si NO está en la colección filtrada
            $yaEstaEnLaLista = $barberosDisponiblesFiltrado->contains('id', $barberoDeCitaEspecifica->id);
    
            if (!$yaEstaEnLaLista) {
                Log::info("Agregando forzosamente al barbero actual ID: {$barberoDeCitaEspecifica->id}");
                
                // Importante: Cargar la relación 'usuario' para que no falle el mapeo posterior
                $barberoDeCitaEspecifica->load('usuario');
                
                // Agregarlo a la colección
                $barberosDisponiblesFiltrado->push($barberoDeCitaEspecifica);
            }
        }
        // Formatear respuesta: solo id y nombre
        $formattedBarberos = $barberosDisponiblesFiltrado->map(function ($barbero) {
            return [
                'id' => $barbero->id,
                'nombre' => $barbero->usuario->name ?? 'N/A',
            ];
        })->values();
        
        return response()->json([
            'barberos' => $formattedBarberos,
        ]);
    }





    //metodo para clientes, ya no utilizado
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
            ->get()
            ->filter(function ($barbero) use ($fechaHoraInicio) {
                // Filtrar por disponibilidad de horario
                return $this->esBarberoDisponibleEnHorario($barbero->id, $fechaHoraInicio);
            });
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
                ->get()
                ->filter(function ($barbero) use ($fechaHoraInicio) {
                    // Filtrar por disponibilidad de horario
                    return $this->esBarberoDisponibleEnHorario($barbero->id, $fechaHoraInicio);
                });
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




    /**
     * Verifica si un barbero está disponible en una fecha y hora específica
     * según sus horarios regulares y excepciones
     */
    private function esBarberoDisponibleEnHorario($barberoId, Carbon $fechaHora)
    {
        // Si el barbero no tiene horarios definidos, asumimos disponibilidad 24/7 (backward compatibility)
        $tieneHorarios = HorarioBarbero::where('barbero_id', $barberoId)->exists();
        
        if (!$tieneHorarios) {
            return true; // Sin horarios = disponible siempre
        }

        $fecha = $fechaHora->format('Y-m-d');
        $hora = $fechaHora->format('H:i:s');
        $diaSemana = $fechaHora->dayOfWeekIso; // 1=Lunes, 7=Domingo

        // Primero verificar si hay una excepción para esta fecha específica
        $excepcion = ExcepcionHorario::where('barbero_id', $barberoId)
            ->where('fecha', $fecha)
            ->first();

        if ($excepcion) {
            // Si no está disponible en la excepción, retornar false
            if (!$excepcion->es_disponible) {
                return false;
            }
            
            // Si está disponible con horario especial, verificar el horario
            if ($excepcion->hora_inicio && $excepcion->hora_fin) {
                return $hora >= $excepcion->hora_inicio && $hora <= $excepcion->hora_fin;
            }
            
            // Si está disponible sin horario especial, retornar true
            return true;
        }

        // Si no hay excepción, verificar el horario regular del día de la semana
        $horarioRegular = HorarioBarbero::where('barbero_id', $barberoId)
            ->where('dia_semana', (string)$diaSemana)
            ->first();

        if (!$horarioRegular) {
            return false; // No trabaja este día de la semana
        }

        // Verificar si la hora está dentro del rango del horario
        return $hora >= $horarioRegular->hora_inicio && $hora <= $horarioRegular->hora_fin;
    }

    /**
     * Webhook handler para recibir notificaciones de pago de PagoFácil
     * Esta ruta debe estar excluida del CSRF protection
     */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('Callback de PagoFácil recibido', ['data' => $request->all()]);

            // PagoFácil envía el UUID en el campo PedidoID
            $pedidoId = $request->input('PedidoID');
            $estado = $request->input('Estado');
            
            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'PedidoID no encontrado',
                    'values' => false
                ]);
            }

            // Buscar la cita por el UUID
            $cita = Cita::where('transaccion_uuid', $pedidoId)->first();

            if (!$cita) {
                Log::error('Cita no encontrada para UUID', ['uuid' => $pedidoId]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Cita no encontrada',
                    'values' => false
                ]);
            }

            // Verificar que el pago fue exitoso
            // PagoFácil envía "Pagado" o código numérico según su documentación
            //if ($estado === 'Pagado' || $estado === 'PAGADO' || $estado == 1) {
            if ($estado == 2 || $estado === 'Pagado' || $estado === 'PAGADO'){
                $cita->estado = 'confirmada';
                $cita->save();

                Log::info('Pago confirmado para cita', [
                    'cita_id' => $cita->id,
                    'uuid' => $pedidoId
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message' => 'Pago recibido correctamente',
                    'values' => true
                ]);
            }

            // Si el estado no es "Pagado", registrar pero no confirmar
            Log::warning('Estado de pago no confirmado', [
                'estado' => $estado,
                'cita_id' => $cita->id
            ]);

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Notificación recibida',
                'values' => true
            ]);

        } catch (Exception $e) {
            Log::error('Error en callback de PagoFácil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error al procesar callback',
                'values' => false
            ]);
        }
    }

    /**
     * Verificar el estado de pago de una cita (para polling desde frontend)
     */
    // public function verificarEstadoPago(string $id)
    // {
    //     try {
    //         $cita = Cita::findOrFail($id);
            
    //         return response()->json([
    //             'estado' => $cita->estado,
    //             'confirmada' => $cita->estado === 'confirmada',
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'error' => 'Cita no encontrada'
    //         ], 404);
    //     }
    // }

    public function verificarEstadoPago(string $id)
    {
        $cita = Cita::findOrFail($id);
        
        // Si el Webhook funcionó, esto será true de inmediato
        if ($cita->estado === 'confirmada') {
            return response()->json(['confirmada' => true, 'estado' => 'confirmada']);
        }

        // SI NO FUNCIONÓ EL WEBHOOK (Plan B): Consultamos manualmente
        $pagoFacilService = new PagoFacilService();
        $respuesta = $pagoFacilService->consultarTransaccion($cita->transaccion_id_pagofacil);

        // Si Pago Fácil nos dice que el estado es 2 (Pagado)
        if (isset($respuesta['values']['status']) && $respuesta['values']['status'] == 2) {
            $cita->update(['estado' => 'confirmada']); // Lo actualizamos nosotros
            return response()->json(['confirmada' => true, 'estado' => 'confirmada']);
        }

        return response()->json(['confirmada' => false, 'estado' => 'pendiente']);
    }
    public function cancelarCitaManual(string $id)
    {
        try {
            $usuarioAutenticado = Auth::user();
            $rolUser = $usuarioAutenticado->rol;
            $cita = Cita::findOrFail($id);

            // if ($cita->cliente_id !== Auth::user()->cliente->id) {
            //     return response()->json(['error' => 'No autorizado'], 403);
            // }

            // Seguridad: Solo permitir cancelar si sigue en estado pendiente
            if ($cita->estado !== 'pendiente') {
                if($rolUser == "propietario" || $rolUser == "barbero"){
                    return redirect()->route('citas-admin.index')
                        ->with('error', 'La cita ya no puede ser cancelada porque no está pendiente.');
                }
                return redirect()->route('citas-cliente.index')
                    ->with('error', 'La cita ya no puede ser cancelada porque no está pendiente.');
            }

            // Cambiar estado a cancelada
            $cita->update([
                'estado' => 'cancelada'
            ]);

            Log::info("Cita cancelada manualmente por el usuario", ['cita_id' => $id]);
            if($rolUser == "propietario" || $rolUser == "barbero"){
                return redirect()->route('citas-admin.index')
                ->with('success', 'La reserva ha sido cancelada y el horario liberado.');
            }
            return redirect()->route('citas-cliente.index')
                ->with('success', 'La reserva ha sido cancelada y el horario liberado.');

        } catch (Exception $e) {
            Log::error("Error al cancelar cita manual: " . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo cancelar la cita.');
        }
    }
}
