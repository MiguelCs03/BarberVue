<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        //ejecutar solamente cuando cambiemos la migracion(SOLO EN DESARROLLO   )
        //php artisan migrate:fresh borra y vuelve a crear la base de datos
        $this->call([
            UserSeeder::class,
            BarberoSeeder::class,
            ClienteSeeder::class,
            ConfiguracionSeeder::class, 
            TipoPagoSeeder::class,
            ServicioSeeder::class,
            ProductoSeeder::class,
            MenuItemSeeder::class,
            ServicioBarberoSeeder::class,
            
        ]);
    }
}
