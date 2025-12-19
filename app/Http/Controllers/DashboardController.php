<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $usuarioAutenticado = Auth::user();
        if($usuarioAutenticado->rol == 'barbero' || $usuarioAutenticado->rol == 'propietario'){
            // Métricas de Usuarios
            $usuarios = [
                'total' => User::count(),
                'clientes' => User::where('rol', 'cliente')->count(),
                'barberos' => User::where('rol', 'barbero')->count(),
            ];

            // Métricas de Productos (Ajustado a columna 'estado' según tu migración)
            $productos = [
                'total' => Producto::count(),
                'activos' => Producto::where('estado', 'activo')->count(),
                'inactivos' => Producto::where('estado', 'inactivo')->count(),
            ];

            // Métricas de Servicios (Ajustado a columna 'estado' según tu migración)
            $servicios = [
                'total' => Servicio::count(),
                'activos' => Servicio::where('estado', 'activo')->count(),
                'inactivos' => Servicio::where('estado', 'inactivo')->count(),
            ];

            // Métricas de Citas
            $citas = [
                'total' => Cita::count(),
                'confirmadas' => Cita::where('estado', 'confirmada')->count(),
                'pendientes' => Cita::where('estado', 'pendiente')->count(),
                'canceladas' => Cita::where('estado', 'cancelada')->count(),
            ];

            // Últimas 3 citas pendientes - CORRECCIÓN DE RELACIÓN (servicio en singular)
            $ultimasCitasPendientes = Cita::with(['cliente.usuario','barbero.usuario', 'citaServicios.servicio'])
                ->where('estado', 'pendiente')
                ->latest()
                ->limit(3)
                ->get()
                ->map(function ($cita) {
                    $primerCitaServicio = $cita->citaServicios->first();
                    return [
                        'id' => $cita->id,
                        'cliente' => $cita->cliente->usuario->name ?? 'Sin nombre',
                        'barbero' => $cita->barbero->usuario->name ?? 'Sin nombre',
                        'fecha' => $cita->fecha,
                        // CORRECCIÓN: Usar ->servicio (singular)
                        'primer_servicio' => $primerCitaServicio->servicio->nombre ?? 'Sin servicio',
                    ];
                });

            return Inertia::render('Dash2', [
                'metrics' => [
                    'usuarios' => $usuarios,
                    'productos' => $productos,
                    'servicios' => $servicios,
                    'citas' => $citas,
                ],
                'ultimasCitasPendientes' => $ultimasCitasPendientes
            ]);
        }

        // SI ES CLIENTE
        $citas = [
                'confirmadas' => Cita::where('estado', 'confirmada')->where('cliente_id',$usuarioAutenticado->id)->count(),
                'pendientes' => Cita::where('estado', 'pendiente')->where('cliente_id',$usuarioAutenticado->id)->count(),
                'canceladas' => Cita::where('estado', 'cancelada')->where('cliente_id',$usuarioAutenticado->id)->count(),
        ];

        // Últimas 3 citas pendientes cliente - CORRECCIÓN DE RELACIÓN (servicio en singular)
        $ultimasCitasPendientes = Cita::with(['cliente.usuario','barbero.usuario', 'citaServicios.servicio'])
                ->where('estado', 'pendiente')
                ->where('cliente_id',$usuarioAutenticado->id)
                ->latest()
                ->limit(3)
                ->get()
                ->map(function ($cita) {
                    $primerCitaServicio = $cita->citaServicios->first();
                    return [
                        'id' => $cita->id,
                        'cliente' => $cita->cliente->usuario->name ?? 'Sin nombre',
                        'barbero' => $cita->barbero->usuario->name ?? 'Sin nombre',
                        'fecha' => $cita->fecha,
                        // CORRECCIÓN: Usar ->servicio (singular)
                        'primer_servicio' => $primerCitaServicio->servicio->nombre ?? 'Sin servicio',
                    ];
                });

        return Inertia::render('Dash2', [
            'metrics' => [
                'citas' => $citas,
            ],
            'ultimasCitasPendientes' => $ultimasCitasPendientes
        ]);
    }
}
