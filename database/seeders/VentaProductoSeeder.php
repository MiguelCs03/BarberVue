<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\Detalle;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Barbero;

class VentaProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            $this->command->error('Debe haber clientes y productos en la base de datos para ejecutar este seeder.');
            return;
        }

        $this->command->info('Generando 30 ventas de productos...');

        for ($i = 0; $i < 30; $i++) {
            $cliente = $clientes->random();

            // Crear la venta inicialmente con monto 0
            $venta = Venta::create([
                'cliente_id' => $cliente->id,
                'barbero_id' => null,
                'monto_total' => 0,
                'estado_pago' => 'completado',
                'cita_id' => null,
            ]);

            $numProductos = rand(1, 4); // Entre 1 y 4 productos por venta
            $montoTotalVenta = 0;

            // Seleccionar productos aleatorios sin repetir en la misma venta
            $productosVenta = $productos->random(min($numProductos, $productos->count()));

            foreach ($productosVenta as $producto) {
                $cantidad = rand(1, 3);
                $subtotal = $producto->precio_venta * $cantidad;

                Detalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'servicio_id' => null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $subtotal,
                ]);

                $montoTotalVenta += $subtotal;

                // Actualizar stock del producto
                #$producto->decrement('stock_actual', $cantidad);
            }

            // Actualizar el monto total de la venta
            $venta->update(['monto_total' => $montoTotalVenta]);
        }

        $this->command->info('30 ventas creadas con éxito.');
    }
}
