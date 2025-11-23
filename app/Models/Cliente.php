<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
    ];

    /**
     * Get the user that owns the cliente
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
