<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id',
    ];

    /**
     * Get the user that owns the cliente
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id','id');
    }
    public function citas(){
        return $this->hasMany(Cita::class, 'cliente_id');
    }
}
