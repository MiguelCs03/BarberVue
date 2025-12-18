<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Iniciando seeder de Configuracion...');
        $configuraciones = [
            [
                'nombre' => 'porcentaje_cita',
                'valor' => '30', 
                'descripcion' => 'Porcentaje de cobro para reservar cita',
            ],
        ];
        foreach ($configuraciones as $configuracion) {
            $config = Configuracion::firstOrCreate(
                ['nombre' => $configuracion['nombre']],
                [
                    'valor' => $configuracion['valor'],
                    'descripcion' => $configuracion['descripcion'],
                ]
            );

            if ($config->wasRecentlyCreated) {
                $this->command->line("  ✅ Creado: {$configuracion['nombre']}");
            } else {
                $this->command->line("  ⏭️  Ya existe: {$configuracion['nombre']}");
            }
            
        }
        
    }
}
