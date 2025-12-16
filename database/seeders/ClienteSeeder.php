<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            'Daniel Martinez',
            'Erick Rodriguez',
            'Alex Fernandez'
        ];

        $this->command->info('Iniciando creación de Clientes...');

        foreach ($clientes as $index => $nombre) {
            $numero = $index + 1;
            $nombreCorto = strtolower(explode(' ', $nombre)[0]); // daniel, erick, alex
            $email = "{$nombreCorto}@gmail.com";

            $this->command->info("Procesando: {$nombre} ({$email})");

            $usuario = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nombre,
                    'password' => Hash::make('123456789'),
                    'rol' => 'cliente',
                ]
            );

            if ($usuario->wasRecentlyCreated) {
                $this->command->info("  + Cliente creado con éxito (ID: {$usuario->id})");
            } else {
                $this->command->warn("  * El cliente ya existía (ID: {$usuario->id}), saltando creación.");
            }

            $cliente = Cliente::firstOrCreate(
                ['id' => $usuario->id],
                
            );

            if ($cliente->wasRecentlyCreated) {
                $this->command->info("  + Perfil de Cliente vinculado correctamente.");
            } else {
                $this->command->warn("  * El perfil de Cliente ya estaba vinculado.");
            }
            
            $this->command->line('-------------------------------------------');
        }

        $this->command->info('¡Proceso de seeding de Clientes finalizado!');
    }
}
