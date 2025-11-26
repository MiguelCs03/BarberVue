<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un usuario admin
        $adminExists = User::where('email', 'admin@barberia.com')->exists();

        if ($adminExists) {
            $this->command->info('El usuario admin ya existe.');
            return;
        }

        // Crear usuario admin (con rol barbero pero sin registro en tabla barberos)
        User::create([
            'name' => 'Admin Propietario',
            'email' => 'admin@barberia.com',
            'password' => Hash::make('admin123'),
            'rol' => 'barbero',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Usuario admin creado exitosamente:');
        $this->command->info('Email: admin@barberia.com');
        $this->command->info('Password: admin123');
        $this->command->warn('IMPORTANTE: Este usuario NO tiene registro en la tabla barberos, por lo que verá la vista administrativa.');
    }
}
