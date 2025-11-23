<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioBarbero extends Model
{
    //
    protected $table = 'servicio_barberos';
    protected $fillable = [
        'barbero_id',
        'servicio_id',
    ];
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
    
    public function barbero()
    {
        return $this->belongsTo(Barbero::class, 'barbero_id');
    }
}
