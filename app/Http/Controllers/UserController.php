<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barbero;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['barbero', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'rol' => $user->rol,
                    'estado' => $user->rol === 'barbero' && $user->barbero 
                        ? $user->barbero->estado_barbero 
                        : 'activo',
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $servicios = \App\Models\Servicio::where('estado', 'activo')
            ->select('id', 'nombre', 'descripcion', 'precio')
            ->get();

        return Inertia::render('Users/Create', [
            'servicios' => $servicios,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol' => ['required', Rule::in(['barbero', 'cliente'])],
            'estado_barbero' => 'nullable|in:disponible,ocupado,descanso',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
        ]);

        // Create role-specific record
        if ($validated['rol'] === 'barbero') {
            $barbero = Barbero::create([
                'usuario_id' => $user->id,
                'estado_barbero' => $validated['estado_barbero'] ?? 'disponible',
            ]);

            // Asociar servicios al barbero
            if (!empty($validated['servicios'])) {
                foreach ($validated['servicios'] as $servicioId) {
                    \App\Models\ServicioBarbero::create([
                        'barbero_id' => $barbero->id,
                        'servicio_id' => $servicioId,
                    ]);
                }
            }
        } else {
            Cliente::create([
                'usuario_id' => $user->id,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with(['barbero', 'cliente'])->findOrFail($id);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol,
            'estado_barbero' => $user->rol === 'barbero' && $user->barbero 
                ? $user->barbero->estado_barbero 
                : null,
            'created_at' => $user->created_at->format('d/m/Y H:i'),
        ];

        return Inertia::render('Users/Show', [
            'user' => $userData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with(['barbero.servicioBarberos', 'cliente'])->findOrFail($id);

        $servicios = \App\Models\Servicio::where('estado', 'activo')
            ->select('id', 'nombre', 'descripcion', 'precio')
            ->get();

        $serviciosSeleccionados = [];
        if ($user->rol === 'barbero' && $user->barbero) {
            $serviciosSeleccionados = $user->barbero->servicioBarberos->pluck('servicio_id')->toArray();
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol,
            'estado_barbero' => $user->rol === 'barbero' && $user->barbero 
                ? $user->barbero->estado_barbero 
                : 'disponible',
            'servicios' => $serviciosSeleccionados,
        ];

        return Inertia::render('Users/Edit', [
            'user' => $userData,
            'servicios' => $servicios,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'rol' => ['required', Rule::in(['barbero', 'cliente'])],
            'estado_barbero' => 'nullable|in:disponible,ocupado,descanso',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Handle role change
        if ($validated['rol'] === 'barbero') {
            // Delete cliente record if exists
            if ($user->cliente) {
                $user->cliente->delete();
            }
            
            // Create or update barbero record
            if ($user->barbero) {
                $user->barbero->update([
                    'estado_barbero' => $validated['estado_barbero'] ?? 'disponible',
                ]);
                $barbero = $user->barbero;
            } else {
                $barbero = Barbero::create([
                    'usuario_id' => $user->id,
                    'estado_barbero' => $validated['estado_barbero'] ?? 'disponible',
                ]);
            }

            // Actualizar servicios del barbero
            // Primero eliminar los servicios actuales
            \App\Models\ServicioBarbero::where('barbero_id', $barbero->id)->delete();
            
            // Luego agregar los nuevos servicios
            if (!empty($validated['servicios'])) {
                foreach ($validated['servicios'] as $servicioId) {
                    \App\Models\ServicioBarbero::create([
                        'barbero_id' => $barbero->id,
                        'servicio_id' => $servicioId,
                    ]);
                }
            }
        } else {
            // Delete barbero record if exists
            if ($user->barbero) {
                $user->barbero->delete();
            }
            
            // Create cliente record if doesn't exist
            if (!$user->cliente) {
                Cliente::create([
                    'usuario_id' => $user->id,
                ]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
