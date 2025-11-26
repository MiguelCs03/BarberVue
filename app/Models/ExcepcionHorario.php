<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcepcionHorario extends Model
{
    protected $table = 'excepciones_horarios';

    protected $fillable = [
        'barbero_id',
        'fecha',
        'es_disponible',
        'hora_inicio',
        'hora_fin',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_disponible' => 'boolean',
    ];

    /**
     * Get the barbero that owns the excepcion
     */
    public function barbero()
    {
        return $this->belongsTo(Barbero::class, 'barbero_id');
    }
}
