<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Porcentaje extends Model
{
    //
    protected $table = 'porcentajes';
    //atributo en masa
    protected $fillable = [
        'estado',
        'porcentaje'
    ];
    public function citas(){
        return $this->hasMany(Cita::class, 'porcentaje_id');    
    }
}
