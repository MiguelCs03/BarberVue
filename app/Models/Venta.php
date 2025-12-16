<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    //
    protected $table = 'ventas';
    protected $fillable = [
        'cita_id',
        'cliente_id',
        'barbero_id',
        'monto_total',
        'estado_pago',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function barbero()
    {
        return $this->belongsTo(Barbero::class);
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class);
    }
    public function detallePagos(){
        return $this->hasMany(DetallePago::class, 'venta_id');    
    }
}
