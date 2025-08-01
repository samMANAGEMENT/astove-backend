<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'role_permiso');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tienePermiso($permiso)
    {
        if (is_string($permiso)) {
            return $this->permisos()->where('nombre', $permiso)->exists();
        }
        
        return $this->permisos()->where('id', $permiso->id)->exists();
    }

    public function tienePermisoModulo($modulo)
    {
        return $this->permisos()->where('modulo', $modulo)->exists();
    }
} 