<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing menu items
        MenuItem::truncate();

        // Dashboard - Visible for all roles
        MenuItem::create([
            'nombre' => 'Dashboard',
            'icono' => 'home',
            'ruta' => '/dashboard',
            'orden' => 1,
            'roles' => null, // All roles
            'activo' => true,
        ]);

        // Users - Only for barbero
        MenuItem::create([
            'nombre' => 'Usuarios',
            'icono' => 'users',
            'ruta' => '/users',
            'orden' => 2,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Inventory Section Header - Only for barbero
        $inventoryHeader = MenuItem::create([
            'nombre' => 'Inventario',
            'icono' => 'folder',
            'ruta' => '#',
            'orden' => 3,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Products - Child of Inventory, only barbero
        MenuItem::create([
            'nombre' => 'Productos',
            'icono' => 'box',
            'ruta' => '/products',
            'orden' => 1,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Services - Child of Inventory, only barbero
        MenuItem::create([
            'nombre' => 'Servicios',
            'icono' => 'scissors',
            'ruta' => '/services',
            'orden' => 2,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Inventory Movements - Child of Inventory, only barbero
        MenuItem::create([
            'nombre' => 'Movimientos',
            'icono' => 'clipboard',
            'ruta' => '/inventory',
            'orden' => 3,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Appointments - For barbero (admin)
        MenuItem::create([
            'nombre' => 'Citas',
            'icono' => 'calendar',
            'ruta' => '/citas-admin/index',
            'orden' => 4,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Appointments - For cliente with submenu
        $citasClienteHeader = MenuItem::create([
            'nombre' => 'Citas',
            'icono' => 'calendar',
            'ruta' => '#',
            'orden' => 4,
            'roles' => 'cliente',
            'activo' => true,
        ]);

        // Create appointment - Child of Citas for cliente
        MenuItem::create([
            'nombre' => 'Crear Cita',
            'icono' => 'plus',
            'ruta' => '/citas-cliente/create',
            'orden' => 1,
            'parent_id' => $citasClienteHeader->id,
            'roles' => 'cliente',
            'activo' => true,
        ]);

        // List appointments - Child of Citas for cliente
        MenuItem::create([
            'nombre' => 'Mis Citas',
            'icono' => 'list',
            'ruta' => '/citas-cliente/index',
            'orden' => 2,
            'parent_id' => $citasClienteHeader->id,
            'roles' => 'cliente',
            'activo' => true,
        ]);

        // Analytics - Only for barbero
        MenuItem::create([
            'nombre' => 'Estadísticas',
            'icono' => 'chart',
            'ruta' => '/analytics/visits',
            'orden' => 5,
            'roles' => 'barbero',
            'activo' => true,
        ]);
    }
}
