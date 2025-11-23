<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Servicio::query();
        //consulta cada que se hace key pressed(podria optmizarse)
        if($request->has('search')){
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }
        $services = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Services/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_estimada' => 'nullable|integer|min:1',
        ]);
    
        Servicio::create($validated);
    
        return redirect()->route('services.index')
            ->with('success', 'Servicio creado exitosamente');
    }

    public function show(string $id)
    {
        $service = Servicio::findOrFail($id);
        return Inertia::render('Services/Show',[
            'service' => $service,
        ]);
    }

    public function edit(string $id)
    {
        $service = Servicio::findOrFail($id);
        return Inertia::render('Services/Edit',[
            'service' => $service,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_estimada' => 'nullable|integer|min:1',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $service = Servicio::findOrFail($id);
        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'Servicio actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('services.index');
    }
}
