<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\Detalle;
use App\Models\DetallePago;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Barbero;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\TipoPago;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class FullTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $barberos = Barbero::with('servicioBarberos.servicio')->get();
        $productos = Producto::all();
        $tiposPago = TipoPago::all();

        if ($clientes->isEmpty() || $barberos->isEmpty() || $tiposPago->isEmpty()) {
            $this->command->error('Faltan maestros (clientes, barberos o tipos de pago) para ejecutar este seeder.');
            return;
        }

        $this->command->info('Generando 50 transacciones mixtas (Ventas Directas y Citas)...');

        for ($i = 0; $i < 50; $i++) {
            $esCita = rand(0, 1); // 50% probabilidad de ser flujo de cita

            if ($esCita) {
                $this->generarFlujoCita($clientes, $barberos, $tiposPago);
            } else {
                $this->generarVentaDirecta($clientes, $barberos, $productos, $tiposPago);
            }
        }

        $this->command->info('¡FullTransactionSeeder completado con éxito!');
    }

    private function generarVentaDirecta($clientes, $barberos, $productos, $tiposPago)
    {
        $cliente = $clientes->random();
        $tipoPuntual = rand(0, 2); // 0: Solo productos, 1: Solo servicios, 2: Ambos

        $barbero = null;
        if ($tipoPuntual > 0) {
            $barbero = $barberos->filter(fn($b) => $b->servicioBarberos->isNotEmpty())->random();
        }

        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'barbero_id' => $barbero ? $barbero->id : null,
            'monto_total' => 0,
            'estado_pago' => 'completado',
            'cita_id' => null,
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
        ]);

        $montoAcumulado = 0;

        // Agregar Productos
        if ($tipoPuntual === 0 || $tipoPuntual === 2) {
            $prods = $productos->random(rand(1, 3));
            foreach ($prods as $p) {
                $cant = rand(1, 2);
                $sub = $p->precio_venta * $cant;
                Detalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $p->id,
                    'servicio_id' => null,
                    'cantidad' => $cant,
                    'precio_unitario' => $p->precio_venta,
                    'subtotal' => $sub
                ]);
                $montoAcumulado += $sub;
                $p->decrement('stock_actual', $cant);
            }
        }

        // Agregar Servicios (validados por barbero)
        if ($tipoPuntual === 1 || $tipoPuntual === 2) {
            $servsDisponibles = $barbero->servicioBarberos->map(fn($sb) => $sb->servicio);
            $servs = $servsDisponibles->random(min(rand(1, 2), $servsDisponibles->count()));
            foreach ($servs as $s) {
                Detalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => null,
                    'servicio_id' => $s->id,
                    'cantidad' => 1,
                    'precio_unitario' => $s->precio,
                    'subtotal' => $s->precio
                ]);
                $montoAcumulado += $s->precio;
            }
        }

        $venta->update(['monto_total' => $montoAcumulado]);

        // Registro de pago único
        DetallePago::create([
            'venta_id' => $venta->id,
            'tipo_pago_id' => $tiposPago->random()->id,
            'monto' => $montoAcumulado,
            'created_at' => $venta->created_at,
        ]);
    }

    private function generarFlujoCita($clientes, $barberos, $tiposPago)
    {
        $barbero = $barberos->filter(fn($b) => $b->servicioBarberos->isNotEmpty())->random();
        $cliente = $clientes->random();
        $servsDisponibles = $barbero->servicioBarberos->map(fn($sb) => $sb->servicio);
        $servsElegidos = $servsDisponibles->random(min(rand(1, 3), $servsDisponibles->count()));

        $montoServicios = $servsElegidos->sum('precio');
        $porcentaje = rand(20, 40) / 100;
        $pagoInicial = round($montoServicios * $porcentaje, 2);

        // 1. Crear Cita
        $fechaCita = Carbon::now()->subDays(rand(0, 30))->setTime(rand(9, 18), 0);
        $cita = Cita::create([
            'cliente_id' => $cliente->id,
            'barbero_id' => $barbero->id,
            'tipo_pago_id' => $tiposPago->random()->id,
            'fecha' => $fechaCita,
            'estado' => 'completada',
            'pago_inicial' => $pagoInicial,
            'monto_total' => $montoServicios,
            'porcentaje_cita' => $porcentaje,
        ]);

        foreach ($servsElegidos as $s) {
            CitaServicio::create([
                'cita_id' => $cita->id,
                'servicio_id' => $s->id
            ]);
        }

        // 2. Crear Venta vinculada
        $venta = Venta::create([
            'cita_id' => $cita->id,
            'cliente_id' => $cliente->id,
            'barbero_id' => $barbero->id,
            'monto_total' => $montoServicios,
            'estado_pago' => 'completado',
            'created_at' => $fechaCita->addHours(1), // Venta ocurre después de la cita
        ]);

        // Reflejar servicios en el detalle de la venta
        foreach ($servsElegidos as $s) {
            Detalle::create([
                'venta_id' => $venta->id,
                'producto_id' => null,
                'servicio_id' => $s->id,
                'cantidad' => 1,
                'precio_unitario' => $s->precio,
                'subtotal' => $s->precio
            ]);
        }

        // 3. Registrar Pagos
        // Pago Inicial (Cita)
        DetallePago::create([
            'venta_id' => $venta->id,
            'tipo_pago_id' => $cita->tipo_pago_id,
            'monto' => $pagoInicial,
            'created_at' => $fechaCita->subMinutes(30), // El adelanto fue antes
        ]);

        // Pago Restante (Venta)
        $montoRestante = $montoServicios - $pagoInicial;
        if ($montoRestante > 0) {
            DetallePago::create([
                'venta_id' => $venta->id,
                'tipo_pago_id' => $tiposPago->random()->id,
                'monto' => $montoRestante,
                'created_at' => $venta->created_at,
            ]);
        }
    }
}
