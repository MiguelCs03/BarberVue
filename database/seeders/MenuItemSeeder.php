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
            'ruta' => 'dashboard', // ✅ CORRECTO: Ya usaba el nombre de ruta
            'orden' => 1,
            'roles' => null, 
            'activo' => true,
        ]);

        // Users - Only for barbero
        MenuItem::create([
            'nombre' => 'Usuarios',
            'icono' => 'users',
            'ruta' => 'users.index', // ✅ CORREGIDO: Usamos el nombre de la ruta resource
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
            'ruta' => 'products.index', // ✅ CORREGIDO: Usamos el nombre de la ruta resource
            'orden' => 1,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Services - Child of Inventory, only barbero
        MenuItem::create([
            'nombre' => 'Servicios',
            'icono' => 'scissors',
            'ruta' => 'services.index', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 2,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Inventory Movements - Child of Inventory, only barbero
        MenuItem::create([
            'nombre' => 'Movimientos',
            'icono' => 'clipboard',
            'ruta' => 'movimientos.index', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 3,
            'parent_id' => $inventoryHeader->id,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Appointments - For barbero (admin)
        MenuItem::create([
            'nombre' => 'Citas (Admin)',
            'icono' => 'calendar',
            'ruta' => 'citas-admin.index', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 4,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Appointments - For cliente with submenu
        $citasClienteHeader = MenuItem::create([
            'nombre' => 'Citas (Cliente)',
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
            'ruta' => 'citas-cliente.create', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 1,
            'parent_id' => $citasClienteHeader->id,
            'roles' => 'cliente',
            'activo' => true,
        ]);

        // List appointments - Child of Citas for cliente
        MenuItem::create([
            'nombre' => 'Mis Citas',
            'icono' => 'list',
            'ruta' => 'citas-cliente.index', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 2,
            'parent_id' => $citasClienteHeader->id,
            'roles' => 'cliente',
            'activo' => true,
        ]);

        // Horarios - Only for barbero
        MenuItem::create([
            'nombre' => 'Horarios',
            'icono' => 'clock',
            'ruta' => 'horarios.index', // ✅ CORREGIDO: Usamos el nombre de la ruta resource
            'orden' => 6,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Reports - Only for barbero
        MenuItem::create([
            'nombre' => 'Estadísticas',
            'icono' => 'chart',
            'ruta' => 'bi.v2', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 5,
            'roles' => 'barbero',
            'activo' => true,
        ]);

        // Analytics (estadísticas de visitas) - Only for barbero
        MenuItem::create([
            'nombre' => 'Estadísticas de Visitas',
            'icono' => 'chart',
            'ruta' => 'analytics.visits', // ✅ CORREGIDO: Usamos el nombre de la ruta
            'orden' => 7,
            'roles' => 'barbero',
            'activo' => true,
        ]);
    }
}