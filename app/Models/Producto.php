<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';


    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
    ];

    /**
     * Get the inventory movements for the product
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
    public function detalles(){
        return $this->hasMany(Detalle::class, 'producto_id');    
    }
}
