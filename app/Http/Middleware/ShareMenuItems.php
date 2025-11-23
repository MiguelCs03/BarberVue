<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MenuItem;
use Inertia\Inertia;

class ShareMenuItems
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Share menu items with all Inertia responses
        Inertia::share([
            'menuItems' => function () use ($request) {
                $user = $request->user();
                
                if (!$user) {
                    return [];
                }

                // Get all active parent menu items
                $menuItems = MenuItem::active()
                    ->parents()
                    ->with('children')
                    ->orderBy('orden')
                    ->get()
                    ->filter(function ($item) use ($user) {
                        return $item->hasAccess($user->rol ?? 'cliente');
                    })
                    ->map(function ($item) use ($user) {
                        // Filter children by role
                        $children = $item->children->filter(function ($child) use ($user) {
                            return $child->activo && $child->hasAccess($user->rol ?? 'cliente');
                        })->values();

                        return [
                            'id' => $item->id,
                            'nombre' => $item->nombre,
                            'icono' => $item->icono,
                            'ruta' => $item->ruta,
                            'orden' => $item->orden,
                            'children' => $children->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'nombre' => $child->nombre,
                                    'icono' => $child->icono,
                                    'ruta' => $child->ruta,
                                    'orden' => $child->orden,
                                ];
                            }),
                        ];
                    })
                    ->values();

                return $menuItems;
            },
        ]);

        return $next($request);
    }
}
