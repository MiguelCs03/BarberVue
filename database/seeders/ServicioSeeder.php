<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->command->info('🔄 Iniciando seeder de servicios...');
        $servicios = [
            [
                'nombre' => 'Corte de cabello',
                'descripcion' => 'Corte clásico de caballero',
                'duracion_estimada' => 30,
                'precio' => 15.00,
            ],
            [
                'nombre' => 'Afeitado',
                'descripcion' => 'Afeitado con navaja y toalla caliente',
                'duracion_estimada' => 20,
                'precio' => 10.00,
            ],
            [
                'nombre' => 'Corte + Barba',
                'descripcion' => 'Corte y arreglo de barba',
                'duracion_estimada' => 45,
                'precio' => 25.00,
            ],
            [
                'nombre' => 'Coloración',
                'descripcion' => 'Tinte y tratamiento',
                'duracion_estimada' => 60,
                'precio' => 40.00,
            ],
            [
                'nombre' => 'Tratamiento capilar',
                'descripcion' => 'Mascarilla y secado profesional',
                'duracion_estimada' => 40,
                'precio' => 20.00,
            ],
            [
                'nombre' => 'Perfilado de barba',
                'descripcion' => 'Perfilado detalle',
                'duracion_estimada' => 15,
                'precio' => 8.00,
            ],
            [
                'nombre' => 'Lavado express',
                'descripcion' => 'Lavado y secado rápido',
                'duracion_estimada' => 15,
                'precio' => 6.00,
            ],
            [
                'nombre' => 'Masaje craneal',
                'descripcion' => 'Masaje relajante de cuero cabelludo',
                'duracion_estimada' => 20,
                'precio' => 12.00,
            ],
        ];
        $creados = 0;
        $existentes = 0;
        foreach ($servicios as $servicio) {
            $resultado = Servicio::firstOrCreate(
                ['nombre' => $servicio['nombre']],
                $servicio
            );
            
            if ($resultado->wasRecentlyCreated) {
                $this->command->line("  ✅ Creado: {$servicio['nombre']}");
                $creados++;
            } else {
                $this->command->line("  ⏭️  Ya existe: {$servicio['nombre']}");
                $existentes++;
            }
        }
        $this->command->info("✨ Seeder completado: {$creados} creados, {$existentes} ya existían");
    }
}
