<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'motivo',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * Get the product that owns the movement
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Boot method to update product stock automatically
     */
    protected static function booted()
    {
        static::created(function ($movimiento) {
            $producto = $movimiento->producto;
            
            if ($movimiento->tipo_movimiento === 'entrada') {
                $producto->stock_actual += $movimiento->cantidad;
            } elseif ($movimiento->tipo_movimiento === 'salida') {
                $producto->stock_actual -= $movimiento->cantidad;
            } elseif ($movimiento->tipo_movimiento === 'ajuste') {
                $producto->stock_actual = $movimiento->cantidad;
            }
            
            $producto->save();
        });
    }
}
