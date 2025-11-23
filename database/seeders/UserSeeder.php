<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Barbero;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a barbero user
        $barberoUser = User::create([
            'name' => 'Juan Pérez',
            'email' => 'barbero@barbershop.com',
            'password' => Hash::make('password123'),
            'rol' => 'barbero',
        ]);

        // Create barbero record
        Barbero::create([
            'usuario_id' => $barberoUser->id,
            'estado_barbero' => 'disponible',
        ]);

        // Create a cliente user
        $clienteUser = User::create([
            'name' => 'María González',
            'email' => 'cliente@barbershop.com',
            'password' => Hash::make('password123'),
            'rol' => 'cliente',
        ]);

        // Create cliente record
        Cliente::create([
            'usuario_id' => $clienteUser->id,
        ]);

        // Update existing admin user to barbero if exists
        $adminUser = User::where('email', 'admin@barbershop.com')->first();
        if ($adminUser) {
            $adminUser->update(['rol' => 'barbero']);
            
            // Create barbero record if doesn't exist
            if (!$adminUser->barbero) {
                Barbero::create([
                    'usuario_id' => $adminUser->id,
                    'estado_barbero' => 'disponible',
                ]);
            }
        }
    }
}
