<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barbero extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'estado_barbero',
    ];

    /**
     * Get the user that owns the barbero
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function servicioBarberos()
    {
        return $this->hasMany(ServicioBarbero::class, 'barbero_id');
    }
    public function citas(){
        return $this->hasMany(Cita::class, 'barbero_id');
    }
    
}
