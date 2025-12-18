<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventarios';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'motivo',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha' => 'datetime',
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
            if ($movimiento->estado !== 'activo') return;

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

        static::updated(function ($movimiento) {
            // Manejar anulación: revertir el efecto en el stock
            if ($movimiento->isDirty('estado') && $movimiento->estado === 'anulado' && $movimiento->getOriginal('estado') === 'activo') {
                $producto = $movimiento->producto;

                if ($movimiento->tipo_movimiento === 'entrada') {
                    $producto->stock_actual -= $movimiento->cantidad;
                } elseif ($movimiento->tipo_movimiento === 'salida') {
                    $producto->stock_actual += $movimiento->cantidad;
                }
                // Nota: para 'ajuste', revertir es complejo sin el valor anterior.
                // Se asume que ajustes no se "anulan" fácilmente o requieren un nuevo ajuste.

                $producto->save();
            }
        });
    }
}
