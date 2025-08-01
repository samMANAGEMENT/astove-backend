<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'modulo',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permiso');
    }
} 