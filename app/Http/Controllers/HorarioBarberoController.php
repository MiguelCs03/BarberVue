<?php

namespace App\Http\Controllers;

use App\Models\Barbero;
use App\Models\ExcepcionHorario;
use App\Models\HorarioBarbero;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HorarioBarberoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarioAutenticado = Auth::user();
        
        // Si es admin/propietario, mostrar vista administrativa
        if ($usuarioAutenticado->rol === 'barbero' && !$usuarioAutenticado->barbero) {
            return $this->indexAdmin();
        }
        
        // Si es barbero, mostrar su propia vista
        $barbero = $usuarioAutenticado->barbero;

        if (!$barbero) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        $horarios = HorarioBarbero::where('barbero_id', $barbero->id)
            ->orderBy('dia_semana')
            ->get()
            ->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'dia_semana' => $horario->dia_semana,
                   // 'dia_nombre' => $horario->dia_nombre,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                ];
            });

        $excepciones = ExcepcionHorario::where('barbero_id', $barbero->id)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($excepcion) {
                return [
                    'id' => $excepcion->id,
                    'fecha' => Carbon::parse($excepcion->fecha)->format('d/m/Y'),
                    'fecha_raw' => $excepcion->fecha,
                    'es_disponible' => $excepcion->es_disponible,
                    'hora_inicio' => $excepcion->hora_inicio,
                    'hora_fin' => $excepcion->hora_fin,
                    'motivo' => $excepcion->motivo,
                ];
            });

        return Inertia::render('Horarios/Index', [
            'horarios' => $horarios,
            'excepciones' => $excepciones,
            'esAdmin' => false,
        ]);
    }

    /**
     * Vista administrativa para gestionar horarios de todos los barberos
     */
    private function indexAdmin()
    {
        // Obtener todos los barberos con sus horarios
        $barberos = Barbero::with(['usuario', 'horarios', 'excepciones'])
            ->where('estado_barbero', 'disponible')
            ->get()
            ->map(function ($barbero) {
                return [
                    'id' => $barbero->id,
                    'nombre' => $barbero->usuario->name ?? 'N/A',
                    'horarios' => $barbero->horarios->map(function ($horario) {
                        return [
                            'id' => $horario->id,
                            'dia_semana' => $horario->dia_semana,
                            //'dia_nombre' => $horario->dia_nombre,
                            'hora_inicio' => $horario->hora_inicio,
                            'hora_fin' => $horario->hora_fin,
                        ];
                    })->sortBy('dia_semana')->values(),
                    'excepciones' => $barbero->excepciones->map(function ($excepcion) {
                        return [
                            'id' => $excepcion->id,
                            'fecha' => Carbon::parse($excepcion->fecha)->format('d/m/Y'),
                            'fecha_raw' => $excepcion->fecha,
                            'es_disponible' => $excepcion->es_disponible,
                            'hora_inicio' => $excepcion->hora_inicio,
                            'hora_fin' => $excepcion->hora_fin,
                            'motivo' => $excepcion->motivo,
                        ];
                    })->sortByDesc('fecha_raw')->values(),
                ];
            });

        return Inertia::render('Horarios/Index', [
            'barberos' => $barberos,
            'esAdmin' => true,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Horarios/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barbero_id' => 'nullable|exists:barberos,id', // Para admins
            'dia_semana' => 'required|in:1,2,3,4,5,6,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);
    
        
        $usuarioAutenticado = Auth::user();
        //validacion por si viene numerico
        $validated['dia_semana'] = $this->getDiaNombre($validated['dia_semana']);
        // Determinar el barbero_id
        if (isset($validated['barbero_id'])) {
            // Admin asignando horario a un barbero
            $barberoId = $validated['barbero_id'];
        } else {
            // Barbero creando su propio horario
            $barbero = $usuarioAutenticado->barbero;
            if (!$barbero) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción.');
            }
            $barberoId = $barbero->id;
        }

        try {
            // Verificar si ya existe un horario para este día
            $horarioExistente = HorarioBarbero::where('barbero_id', $barberoId)
                ->where('dia_semana', $validated['dia_semana'])
                ->first();

            if ($horarioExistente) {
                return redirect()->back()
                    ->with('error', 'Ya existe un horario para este día. Por favor, edítalo en lugar de crear uno nuevo.')
                    ->withInput();
            }

            HorarioBarbero::create([
                'barbero_id' => $barberoId,
                'dia_semana' => $validated['dia_semana'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
            ]);

            return redirect()->route('horarios.index')
                ->with('success', 'Horario creado exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al crear horario', [
                'error' => $e->getMessage(),
                'barbero_id' => $barberoId,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear el horario. Por favor intenta nuevamente.')
                ->withInput();
        }
    }
    private function getDiaNombre(int $dia): string
    {
        return match ($dia) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo',
            default => 'Desconocido',
        };
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
        $horario = HorarioBarbero::findOrFail($id);

        // Verificar que el horario pertenece al barbero autenticado
        $usuarioAutenticado = Auth::user();
        $barbero = $usuarioAutenticado->barbero;

        if (!$barbero || $horario->barbero_id !== $barbero->id) {
            return redirect()->route('horarios.index')
                ->with('error', 'No tienes permisos para editar este horario.');
        }

        return Inertia::render('Horarios/Edit', [
            'horario' => [
                'id' => $horario->id,
                'dia_semana' => $horario->dia_semana,
                'dia_nombre' => $horario->dia_nombre,
                'hora_inicio' => $horario->hora_inicio,
                'hora_fin' => $horario->hora_fin,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $horario = HorarioBarbero::findOrFail($id);

        $usuarioAutenticado = Auth::user();
        $barbero = $usuarioAutenticado->barbero;

        // Verificar permisos: admin puede editar cualquier horario, barbero solo el suyo
        if ($barbero && $horario->barbero_id !== $barbero->id) {
            return redirect()->route('horarios.index')
                ->with('error', 'No tienes permisos para editar este horario.');
        }

        try {
            $horario->update([
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
            ]);

            return redirect()->route('horarios.index')
                ->with('success', 'Horario actualizado exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al actualizar horario', [
                'error' => $e->getMessage(),
                'horario_id' => $id,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar el horario. Por favor intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $horario = HorarioBarbero::findOrFail($id);

        $usuarioAutenticado = Auth::user();
        $barbero = $usuarioAutenticado->barbero;

        // Verificar permisos: admin puede eliminar cualquier horario, barbero solo el suyo
        if ($barbero && $horario->barbero_id !== $barbero->id) {
            return redirect()->route('horarios.index')
                ->with('error', 'No tienes permisos para eliminar este horario.');
        }

        try {
            $horario->delete();

            return redirect()->route('horarios.index')
                ->with('success', 'Horario eliminado exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al eliminar horario', [
                'error' => $e->getMessage(),
                'horario_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Error al eliminar el horario. Por favor intenta nuevamente.');
        }
    }

    /**
     * Store a new exception
     */
    public function storeExcepcion(Request $request)
    {
        $validated = $request->validate([
            'barbero_id' => 'nullable|exists:barberos,id', // Para admins
            'fecha' => 'required|date|after_or_equal:today',
            'es_disponible' => 'required|boolean',
            'hora_inicio' => 'nullable|required_if:es_disponible,true|date_format:H:i',
            'hora_fin' => 'nullable|required_if:es_disponible,true|date_format:H:i|after:hora_inicio',
            'motivo' => 'nullable|string|max:255',
        ]);

        $usuarioAutenticado = Auth::user();
        
        // Determinar el barbero_id
        if (isset($validated['barbero_id'])) {
            // Admin asignando excepción a un barbero
            $barberoId = $validated['barbero_id'];
        } else {
            // Barbero creando su propia excepción
            $barbero = $usuarioAutenticado->barbero;
            if (!$barbero) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción.');
            }
            $barberoId = $barbero->id;
        }

        try {
            // Verificar si ya existe una excepción para esta fecha
            $excepcionExistente = ExcepcionHorario::where('barbero_id', $barberoId)
                ->where('fecha', $validated['fecha'])
                ->first();

            if ($excepcionExistente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una excepción para esta fecha.')
                    ->withInput();
            }

            ExcepcionHorario::create([
                'barbero_id' => $barberoId,
                'fecha' => $validated['fecha'],
                'es_disponible' => $validated['es_disponible'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'motivo' => $validated['motivo'],
            ]);

            return redirect()->route('horarios.index')
                ->with('success', 'Excepción creada exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al crear excepción', [
                'error' => $e->getMessage(),
                'barbero_id' => $barberoId,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la excepción. Por favor intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Remove an exception
     */
    public function destroyExcepcion(string $id)
    {
        $excepcion = ExcepcionHorario::findOrFail($id);

        $usuarioAutenticado = Auth::user();
        $barbero = $usuarioAutenticado->barbero;

        // Verificar permisos: admin puede eliminar cualquier excepción, barbero solo la suya
        if ($barbero && $excepcion->barbero_id !== $barbero->id) {
            return redirect()->route('horarios.index')
                ->with('error', 'No tienes permisos para eliminar esta excepción.');
        }

        try {
            $excepcion->delete();

            return redirect()->route('horarios.index')
                ->with('success', 'Excepción eliminada exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al eliminar excepción', [
                'error' => $e->getMessage(),
                'excepcion_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Error al eliminar la excepción. Por favor intenta nuevamente.');
        }
    }
}
