<?php

namespace Database\Seeders;

use App\Models\Porcentaje;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PorcentajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->command->info('🔄 Iniciando seeder de porcentaje...');
        $porcentajes = [
            [
                'estado' => 'activo',
                'porcentaje' => 3.00
            ],
        ];
        $creados = 0;
        $existentes = 0;
        foreach ($porcentajes as $porcentajeAAgregar) {
            $resultado = Porcentaje::firstOrCreate(
                ['porcentaje' => $porcentajeAAgregar['porcentaje']],
                $porcentajeAAgregar
            );
            if ($resultado->wasRecentlyCreated) {
                $this->command->line("  ✅ Creado: {$porcentajeAAgregar['porcentaje']}");
                $creados++;

            } else {
                $this->command->line("  ⏭️  Ya existe: {$porcentajeAAgregar['porcentaje']}");
                $existentes++;
            }
        }
        $this->command->info("✨ Seeder completado: {$creados} creados, {$existentes} ya existían");
    }
}
