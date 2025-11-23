<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    //
    protected $table = 'tipo_pagos';
    //atributo en masa
    protected $fillable = [
        'nombre'
    ];
    public function citas(){
        return $this->hasMany(Cita::class, 'tipo_pago_id');    
    }
}
