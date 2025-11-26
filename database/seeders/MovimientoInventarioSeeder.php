<?php

namespace Database\Seeders;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MovimientoInventarioSeeder extends Seeder
{
    public function run(): void
    {
        if (MovimientoInventario::count() >= 50) {
            $this->command->info('Se omitió MovimientoInventarioSeeder: ya existen movimientos suficientes.');
            return;
        }

        $productos = Producto::all();
        if ($productos->isEmpty()) {
            $this->command->warn('No hay productos registrados para generar movimientos de inventario.');
            return;
        }

        $motivosEntrada = ['Reposición de stock', 'Compra mayorista', 'Recepción de proveedor'];
        $motivosSalida = ['Venta mostrador', 'Consumo interno', 'Pedido especial'];
        $motivosAjuste = ['Inventario físico', 'Corrección de stock'];

        $this->command->info('Generando movimientos de inventario de ejemplo...');

        foreach ($productos as $producto) {
            $movimientos = rand(4, 8);

            for ($i = 0; $i < $movimientos; $i++) {
                $tipo = Arr::random(['entrada', 'salida', 'salida', 'ajuste']); // más salidas que entradas
                $cantidad = rand(1, 6);
                $fecha = Carbon::now()->subDays(rand(0, 90))->setTime(rand(8, 20), Arr::random([0, 15, 30, 45]));

                $motivo = match ($tipo) {
                    'entrada' => Arr::random($motivosEntrada),
                    'salida' => Arr::random($motivosSalida),
                    default => Arr::random($motivosAjuste),
                };

                $movimiento = MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => $tipo,
                    'cantidad' => $tipo === 'ajuste' ? max(1, $producto->stock_minimo) : $cantidad,
                    'motivo' => $motivo,
                ]);

                $movimiento->created_at = $fecha;
                $movimiento->updated_at = $fecha;
                $movimiento->save();
            }
        }

        $this->command->info('Movimientos de inventario creados.');
    }
}

