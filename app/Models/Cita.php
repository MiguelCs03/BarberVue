<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    //
    protected $table = 'citas';
    //atributo en masa
    protected $fillable = [
        'estado',
        'fecha',
        'pago_inicial',
        'pago_id',//id de pago facil o stripe
        //foraneas,
        'monto_total',
        'porcentaje_cita',
        'barbero_id',
        'cliente_id',
        'tipo_pago_id'
    ];
    public function citaServicios(){
        return $this->hasMany(CitaServicio::class, 'cita_id');    
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }
    public function barbero(){
        return $this->belongsTo(Barbero::class, 'barbero_id');
    }
    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
