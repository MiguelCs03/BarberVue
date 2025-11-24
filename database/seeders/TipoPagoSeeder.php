<?php

namespace Database\Seeders;

use App\Models\TipoPago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->command->info('🔄 Iniciando seeder de Tipo de Pagos...');
        $tiposPago = [
            [
                'nombre' => 'Efectivo',
                //'activo' => true,
            ],
            [
                'nombre' => 'QR',
                //'activo' => true,
            ],
            [
                'nombre' => 'Stripe',
                //'activo' => true,
            ],
        ];
        foreach ($tiposPago as $tipo) {
            $tipoPago = TipoPago::firstOrCreate(
                ['nombre' => $tipo['nombre']],
            );

            if ($tipoPago->wasRecentlyCreated) {
                $this->command->line("  ✅ Creado: {$tipo['nombre']}");
            } else {
                $this->command->line("  ⏭️  Ya existe: {$tipo['nombre']}");
            }
        }

        $this->command->info("✓ Seeder de tipos de pago completado.");
    }
}
