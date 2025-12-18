<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\Detalle;
use App\Models\Servicio;
use App\Models\Barbero;
use App\Models\Cliente;
use App\Models\ServicioBarbero;

class VentaServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();
        $barberos = Barbero::with('servicioBarberos.servicio')->get();

        if ($clientes->isEmpty() || $barberos->isEmpty()) {
            $this->command->error('Debe haber clientes y barberos con servicios asignados para ejecutar este seeder.');
            return;
        }

        $this->command->info('Generando 50 ventas de servicios distribuidas por barbero...');

        for ($i = 0; $i < 50; $i++) {
            // Seleccionar un barbero al azar que tenga servicios asignados
            $barbero = $barberos->filter(function ($b) {
                return $b->servicioBarberos->isNotEmpty();
            })->random();

            $cliente = $clientes->random();

            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $cliente->id,
                'barbero_id' => $barbero->id,
                'monto_total' => 0,
                'estado_pago' => 'completado',
                'cita_id' => null,
            ]);

            // Obtener los servicios que este barbero ofrece
            $serviciosDisponibles = $barbero->servicioBarberos->map(fn($sb) => $sb->servicio);

            // Determinar cuántos servicios comprará (entre 1 y el total que ofrece el barbero, máximo 3)
            $cantidadServiciosAVender = rand(1, min(3, $serviciosDisponibles->count()));

            $serviciosSeleccionados = $serviciosDisponibles->random($cantidadServiciosAVender);
            $montoTotalVenta = 0;

            foreach ($serviciosSeleccionados as $servicio) {
                $subtotal = $servicio->precio; // Los servicios suelen ser 1 unidad por venta

                Detalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => null,
                    'servicio_id' => $servicio->id,
                    'cantidad' => 1,
                    'precio_unitario' => $servicio->precio,
                    'subtotal' => $subtotal,
                ]);

                $montoTotalVenta += $subtotal;
            }

            // Actualizar el monto total
            $venta->update(['monto_total' => $montoTotalVenta]);
        }

        $this->command->info('50 ventas de servicios creadas correctamente asegurando la compatibilidad Barbero-Servicio.');
    }
}
