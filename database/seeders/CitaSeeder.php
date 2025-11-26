<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\TipoPago;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        if (Cita::count() >= 40) {
            $this->command->info('Se omitió CitaSeeder porque ya existen suficientes registros.');
            return;
        }

        $clientes = Cliente::pluck('id');
        $barberos = Barbero::pluck('id');
        $servicios = Servicio::all();
        $tiposPago = TipoPago::pluck('id');

        if ($clientes->isEmpty() || $barberos->isEmpty() || $servicios->isEmpty() || $tiposPago->isEmpty()) {
            $this->command->warn('No se encontró información suficiente para generar citas de ejemplo.');
            return;
        }

        $this->command->info('Generando citas aleatorias para alimentar los reportes...');

        for ($i = 0; $i < 60; $i++) {
            $fecha = Carbon::now()->subDays(rand(0, 150))->setTime(rand(8, 19), Arr::random([0, 15, 30, 45]));
            $estado = Arr::random(['pendiente', 'pendiente', 'confirmada', 'completada', 'cancelada']);
            if ($fecha->isFuture()) {
                $estado = 'pendiente';
            }

            $serviciosAsignados = $servicios->random(min(rand(1, 3), $servicios->count()));
            $montoTotal = (float) $serviciosAsignados->sum('precio');
            $porcentaje = Arr::random([0.2, 0.25, 0.3, 0.4]);
            $pagoInicial = round($montoTotal * $porcentaje, 2);

            $cita = Cita::create([
                'cliente_id' => $clientes->random(),
                'barbero_id' => $barberos->random(),
                'tipo_pago_id' => $tiposPago->random(),
                'fecha' => $fecha,
                'estado' => $estado,
                'monto_total' => $montoTotal,
                'pago_inicial' => $pagoInicial,
                'porcentaje_cita' => $porcentaje,
            ]);

            foreach ($serviciosAsignados as $servicio) {
                CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $servicio->id,
                ]);
            }
        }

        $this->command->info('Citas generadas exitosamente.');
    }
}

