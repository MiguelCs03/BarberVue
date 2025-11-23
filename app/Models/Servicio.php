<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    //
    //nombre en lka bd
    protected $table = 'servicios';
    //atributo en masa
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_estimada',
        'estado',
    ];
    //casteo - castesexo
    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_estimada' => 'integer',
    ];

    public function servicioBarberos()
    {
        return $this->hasMany(ServicioBarbero::class, 'servicio_id');
    }
    
    public function citaServicios(){
        return $this->hasMany(CitaServicio::class, 'servicio_id');    
    }
    
}
