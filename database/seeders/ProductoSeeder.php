<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'Shampoo Profesional',
                'descripcion' => 'Shampoo de alta calidad para todo tipo de cabello',
                'precio_venta' => 25.00,
                'stock_actual' => 50,
                'stock_minimo' => 10,
            ],
            [
                'nombre' => 'Cera para Cabello',
                'descripcion' => 'Cera modeladora con fijación fuerte',
                'precio_venta' => 18.00,
                'stock_actual' => 30,
                'stock_minimo' => 5,
            ],
            [
                'nombre' => 'Gel Fijador',
                'descripcion' => 'Gel de fijación extra fuerte',
                'precio_venta' => 15.00,
                'stock_actual' => 25,
                'stock_minimo' => 8,
            ],
            [
                'nombre' => 'Aceite para Barba',
                'descripcion' => 'Aceite nutritivo para barba',
                'precio_venta' => 22.00,
                'stock_actual' => 15,
                'stock_minimo' => 5,
            ],
            [
                'nombre' => 'Navaja de Afeitar',
                'descripcion' => 'Navaja profesional de acero inoxidable',
                'precio_venta' => 45.00,
                'stock_actual' => 8,
                'stock_minimo' => 3,
            ],
            [
                'nombre' => 'Toalla de Barbería',
                'descripcion' => 'Toalla de microfibra premium',
                'precio_venta' => 12.00,
                'stock_actual' => 40,
                'stock_minimo' => 15,
            ],
            [
                'nombre' => 'Espuma de Afeitar',
                'descripcion' => 'Espuma suave para afeitado perfecto',
                'precio_venta' => 10.00,
                'stock_actual' => 35,
                'stock_minimo' => 10,
            ],
            [
                'nombre' => 'Loción After Shave',
                'descripcion' => 'Loción calmante post-afeitado',
                'precio_venta' => 20.00,
                'stock_actual' => 20,
                'stock_minimo' => 5,
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
