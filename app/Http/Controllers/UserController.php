<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barbero;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\ServicioBarbero;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'estado' => $user->estado ,
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
        $servicios = Servicio::where('estado', 'activo')
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
            'apellido' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'telefono' => 'nullable|string|max:40',
            'password' => 'required|string|min:8|confirmed',
            'rol' => ['required', Rule::in(['barbero', 'cliente', 'administrador'])],
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear Usuario Base
            $user = User::create([
                'name' => $validated['name'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'password' => Hash::make($validated['password']),
                'rol' => $validated['rol'],
            ]);

            // 2. Lógica según Rol
            if ($validated['rol'] === 'barbero') {
                $barbero = Barbero::create([
                    'id' => $user->id,
                    
                ]);

                // Asociar servicios iniciales
                if (!empty($validated['servicios'])) {
                    $serviciosData = collect($validated['servicios'])->map(fn($sid) => [
                        'barbero_id' => $barbero->id,
                        'servicio_id' => $sid,
                    ]);
                    ServicioBarbero::insert($serviciosData->toArray());
                }
            } 
            
            if ($validated['rol'] === 'cliente') {
                Cliente::create(['id' => $user->id]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al crear: ' . $e->getMessage()])->withInput();
        }
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
            'apellido' => $user->apellido,
            'telefono' => $user->telefono,
            'email' => $user->email,
            'rol' => $user->rol,
            'estado_barbero' => $user->rol === 'barbero' && $user->barbero 
                ? $user->barbero->estado_barbero 
                : null,
            'created_at' => $user->created_at, // Enviamos el objeto para que el Helper lo formatee
            'updated_at' => $user->updated_at,
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

        $servicios = Servicio::where('estado', 'activo')
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
            'apellido' => $user->apellido,
            'telefono' => $user->telefono,
            'rol' => $user->rol,
            'estado' => $user->estado, 
            'estado_barbero' => $user->rol === 'barbero' ? $user->barbero->estado_barbero : 'disponible',
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
            'apellido' => 'nullable|string|max:100',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'telefono' => 'nullable|string|max:40',
            'estado' => 'required|in:activo,inactivo',
            'rol' => ['required', Rule::in(['administrador', 'barbero', 'cliente'])],
            'password' => 'nullable|string|min:8|confirmed',
            'estado_barbero' => 'nullable|in:disponible,ocupado,descanso',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);
        

        DB::beginTransaction();

        try {
            // 1. Actualizar Usuario Base
            // Asegúrate de que estos campos estén en el protected $fillable del modelo User
            $user->name = $validated['name'];
            $user->apellido = $validated['apellido'];
            $user->email = $validated['email'];
            $user->telefono = $validated['telefono'];
            $user->estado = $validated['estado'];
            $user->rol = $validated['rol'];
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            if (!$user->save()) {
                throw new Exception("Error al actualizar los datos básicos del usuario.");
            }

            // 2. Lógica de Barbero y Servicios
            if ($validated['rol'] === 'barbero') {
                $barbero = Barbero::firstOrCreate(['id' => $user->id]);

                if (isset($validated['estado_barbero'])) {
                    $barbero->estado_barbero = $validated['estado_barbero'];
                    $barbero->save();
                }

                $serviciosNuevos = $validated['servicios'] ?? [];
                $serviciosActuales = $barbero->servicioBarberos()->pluck('servicio_id')->toArray();

                // Eliminar los que ya no están
                $aEliminar = array_diff($serviciosActuales, $serviciosNuevos);
                if (!empty($aEliminar)) {
                    $barbero->servicioBarberos()->whereIn('servicio_id', $aEliminar)->delete();
                }

                // Agregar los nuevos
                $aAgregar = array_diff($serviciosNuevos, $serviciosActuales);
                if (!empty($aAgregar)) {
                    foreach ($aAgregar as $sid) {
                        ServicioBarbero::create([
                            'barbero_id' => $barbero->id,
                            'servicio_id' => $sid,
                        ]);
                    }
                }
            }

            // 3. Lógica de Cliente
            if ($validated['rol'] === 'cliente') {
                Cliente::firstOrCreate(['id' => $user->id]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Usuario actualizado con éxito.');

        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Error en la base de datos: ' . $e->getMessage()])
                ->withInput();
        }
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



    public function buscarUsuarioPorUsername(Request $request)
    {

        $validated = $request->validate([
            'username' => 'required|string', 
        ]);
        
        $termino = $validated['username'];

        
        $clientes = Cliente::with('usuario:id,name,email') 
            ->whereHas('usuario', function ($query) use ($termino) {
                $query->where('name', 'LIKE', "%{$termino}%");
            })
            ->paginate(10); 
        $clientes->through(function ($cliente) {
            return [
                'id' => $cliente->id,  
                'name' => $cliente->usuario->name ?? 'Sin Nombre', 
                'email' => $cliente->usuario->email ?? 'Sin Email', 
            ];
        });
        return response()->json($clientes);
    }
    public function buscarUsuarioPorEmail(Request $request){
        $validated = $request->validate([
            'email' => 'required|string', 
        ]);
        
        $termino = $validated['email'];

        $clientes = Cliente::with('usuario:id,name,email') 
            ->whereHas('usuario', function ($query) use ($termino) {

                $query->where('email', 'LIKE', "%{$termino}%");
            })
            ->paginate(10); // Paginación
        $clientes->through(function ($cliente) {
            return [
                'id' => $cliente->id,  
                'name' => $cliente->usuario->name ?? 'Sin Nombre', 
                'email' => $cliente->usuario->email ?? 'Sin Email',
            ];
        });
        return response()->json($clientes);

    }
}
