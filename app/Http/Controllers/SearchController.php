<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];
        $query = strtolower($query);

        // Search in static pages/views
        $pages = $this->getStaticPages();
        foreach ($pages as $page) {
            if (
                str_contains(strtolower($page['title']), $query) ||
                str_contains(strtolower($page['description']), $query) ||
                str_contains(strtolower($page['keywords']), $query)
            ) {
                $results[] = $page;
            }
        }

        // Search Users
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'id' => 'user-' . $user->id,
                'title' => $user->name,
                'description' => $user->email . ' - ' . ucfirst($user->rol),
                'category' => 'Usuarios',
                'url' => route('users.show', $user->id),
            ];
        }

        // TODO: Add Products, Services, Appointments when backend is ready

        return response()->json($results);
    }

    private function getStaticPages()
    {
        return [
            // Dashboard
            [
                'id' => 'page-dashboard',
                'title' => 'Dashboard',
                'description' => 'Panel principal con estadísticas y accesos rápidos',
                'keywords' => 'inicio panel estadisticas resumen',
                'category' => 'Páginas',
                'url' => route('dashboard'),
            ],

            // Users
            [
                'id' => 'page-users',
                'title' => 'Lista de Usuarios',
                'description' => 'Ver todos los usuarios del sistema',
                'keywords' => 'usuarios lista barberos clientes gestión',
                'category' => 'Páginas',
                'url' => route('users.index'),
            ],
            [
                'id' => 'page-users-create',
                'title' => 'Crear Usuario',
                'description' => 'Agregar un nuevo usuario al sistema',
                'keywords' => 'nuevo usuario crear agregar registrar',
                'category' => 'Páginas',
                'url' => route('users.create'),
            ],

            // Products
            [
                'id' => 'page-products',
                'title' => 'Lista de Productos',
                'description' => 'Ver todos los productos del inventario',
                'keywords' => 'productos inventario stock lista',
                'category' => 'Páginas',
                'url' => route('products.index'),
            ],
            [
                'id' => 'page-products-create',
                'title' => 'Crear Producto',
                'description' => 'Agregar un nuevo producto al inventario',
                'keywords' => 'nuevo producto crear agregar inventario',
                'category' => 'Páginas',
                'url' => route('products.create'),
            ],

            // Services
            [
                'id' => 'page-services',
                'title' => 'Lista de Servicios',
                'description' => 'Ver todos los servicios disponibles',
                'keywords' => 'servicios lista corte barba tratamiento',
                'category' => 'Páginas',
                'url' => route('services.index'),
            ],
            [
                'id' => 'page-services-create',
                'title' => 'Crear Servicio',
                'description' => 'Agregar un nuevo servicio',
                'keywords' => 'nuevo servicio crear agregar',
                'category' => 'Páginas',
                'url' => route('services.create'),
            ],

            // Inventory
            [
                'id' => 'page-inventory',
                'title' => 'Movimientos de Inventario',
                'description' => 'Ver historial de movimientos del inventario',
                'keywords' => 'inventario movimientos entradas salidas ajustes',
                'category' => 'Páginas',
                'url' => route('inventory.index'),
            ],
            [
                'id' => 'page-inventory-create',
                'title' => 'Registrar Movimiento',
                'description' => 'Registrar entrada o salida de inventario',
                'keywords' => 'movimiento inventario entrada salida ajuste',
                'category' => 'Páginas',
                'url' => route('inventory.create'),
            ],

            // Appointments
            [
                'id' => 'page-appointments',
                'title' => 'Lista de Citas',
                'description' => 'Ver todas las citas agendadas',
                'keywords' => 'citas agenda reservas turnos',
                'category' => 'Páginas',
                'url' => route('appointments.index'),
            ],
            [
                'id' => 'page-appointments-create',
                'title' => 'Agendar Cita',
                'description' => 'Crear una nueva cita',
                'keywords' => 'nueva cita agendar reservar turno',
                'category' => 'Páginas',
                'url' => route('appointments.create'),
            ],

            // Profile
            [
                'id' => 'page-profile',
                'title' => 'Mi Perfil',
                'description' => 'Ver y editar información de perfil',
                'keywords' => 'perfil cuenta configuración datos personales',
                'category' => 'Páginas',
                'url' => route('profile.edit'),
            ],

            // Analytics
            [
                'id' => 'page-analytics',
                'title' => 'Estadísticas de Visitas',
                'description' => 'Ver estadísticas de visitas a páginas',
                'keywords' => 'estadísticas analytics visitas métricas',
                'category' => 'Páginas',
                'url' => '/analytics/visits',
            ],
        ];
    }
}
