<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaServicio extends Model
{
    //
    protected $table = 'cita_servicios';
    protected $fillable = [
        'cita_id',
        'servicio_id',
    ];
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
