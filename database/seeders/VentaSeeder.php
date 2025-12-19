<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\Detalle;
use App\Models\DetallePago;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\TipoPago;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();
        $barberos = Barbero::all();
        $productos = Producto::where('estado', 'activo')->get();
        $servicios = Servicio::all();
        $tiposPago = TipoPago::all();

        if ($clientes->isEmpty() || $productos->isEmpty() || $servicios->isEmpty() || $tiposPago->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 5; $i++) {
            $venta = Venta::create([
                'cliente_id' => $clientes->random()->id,
                'barbero_id' => $barberos->random()->id,
                'monto_total' => 0,
                'estado_pago' => 'completado',
            ]);

            $total = 0;

            // Add 1-2 products
            foreach ($productos->random(rand(1, 2)) as $prod) {
                $sub = $prod->precio_venta * 1;
                Detalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $prod->id,
                    'cantidad' => 1,
                    'precio_unitario' => $prod->precio_venta,
                    'subtotal' => $sub,
                ]);
                $total += $sub;
            }

            // Add 1 service
            $serv = $servicios->random();
            Detalle::create([
                'venta_id' => $venta->id,
                'servicio_id' => $serv->id,
                'cantidad' => 1,
                'precio_unitario' => $serv->precio,
                'subtotal' => $serv->precio,
            ]);
            $total += $serv->precio;

            $venta->update(['monto_total' => $total]);

            // Add payment
            DetallePago::create([
                'venta_id' => $venta->id,
                'tipo_pago_id' => $tiposPago->random()->id,
                'monto' => $total,
            ]);
        }
    }
}
