<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // barbero or cliente
        'estado',
        'apellido',
        'telefono',
    
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the barbero record if user is a barbero
     */
    public function barbero()
    {
        return $this->hasOne(Barbero::class, 'id');
    }

    /**
     * Get the cliente record if user is a cliente
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id');
    }

    /**
     * Check if user is a barbero
     */
    public function isBarbero()
    {
        return $this->rol === 'barbero';
    }

    /**
     * Check if user is a cliente
     */
    public function isCliente()
    {
        return $this->rol === 'cliente';
    }
}
