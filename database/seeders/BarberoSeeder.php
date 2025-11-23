<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarberoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $nombres = [
            'Carlos Ruiz',
            'Roberto Gomez',
            'Alejandro Silva'
        ];

        $this->command->info('Iniciando creación de Barberos...');

        foreach ($nombres as $index => $nombre) {
            $numero = $index + 1;
            $email = "barbero{$numero}@barbershop.com"; 

            $this->command->info("Procesando: {$nombre} ({$email})");

            
            $usuario = User::firstOrCreate(
                ['email' => $email], // Condición de búsqueda
                [
                    'name' => $nombre,
                    'password' => Hash::make('password123'),
                    'rol' => 'barbero',//esto debe cambiar con el spatie
                ] 
            );

            if ($usuario->wasRecentlyCreated) {
                $this->command->info("  + Usuario creado con éxito (ID: {$usuario->id})");
            } else {
                $this->command->warn("  * El usuario ya existía (ID: {$usuario->id}), saltando creación.");
            }

            
            $barbero = Barbero::firstOrCreate(
                ['usuario_id' => $usuario->id], 
                [
                    'estado_barbero' => 'disponible',
                ]
            );

            if ($barbero->wasRecentlyCreated) {
                $this->command->info("  + Perfil de Barbero vinculado correctamente.");
            } else {
                $this->command->warn("  * El perfil de Barbero ya estaba vinculado.");
            }
            
            $this->command->line('-------------------------------------------');
        }
        
        $this->command->info('¡Proceso de seeding de Barberos finalizado!');
    }
}
