<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barbero extends Model
{
    use HasFactory;
    protected $table = 'barberos';
    public $incrementing = false;
    protected $keyType = 'integer';


    protected $fillable = [
        'id',
        'estado_barbero',
    ];

    /**
     * Get the user that owns the barbero
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id','id');
    }

    public function servicioBarberos()
    {
        return $this->hasMany(ServicioBarbero::class, 'barbero_id');
    }
    public function citas(){
        return $this->hasMany(Cita::class, 'barbero_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioBarbero::class, 'barbero_id');
    }

    public function excepciones()
    {
        return $this->hasMany(ExcepcionHorario::class, 'barbero_id');
    }
    
}
