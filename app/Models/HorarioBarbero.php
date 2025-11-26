<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioBarbero extends Model
{
    protected $table = 'horario_barberos';

    protected $fillable = [
        'barbero_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * Get the barbero that owns the horario
     */
    public function barbero()
    {
        return $this->belongsTo(Barbero::class, 'barbero_id');
    }

    /**
     * Get the day name in Spanish
     */
    public function getDiaNombreAttribute()
    {
        $dias = [
            '1' => 'Lunes',
            '2' => 'Martes',
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo',
        ];
        
        return $dias[$this->dia_semana] ?? 'Desconocido';
    }
}
