<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'icono',
        'ruta',
        'orden',
        'parent_id',
        'roles',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Get the parent menu item
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('orden');
    }

    /**
     * Check if user has access to this menu item
     */
    public function hasAccess($userRole)
    {
        if (empty($this->roles)) {
            return true; // No restrictions
        }

        $allowedRoles = explode(',', $this->roles);
        return in_array($userRole, $allowedRoles);
    }

    /**
     * Scope to get only active menu items
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope to get only parent menu items
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }
}
