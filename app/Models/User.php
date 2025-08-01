<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'operador_id',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function operador()
    {
        return $this->belongsTo(\App\Http\Modules\Operadores\models\Operadores::class, 'operador_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tienePermiso($permiso)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->tienePermiso($permiso);
    }

    public function tienePermisoModulo($modulo)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->tienePermisoModulo($modulo);
    }

    public function esAdmin()
    {
        return $this->role && $this->role->nombre === 'admin';
    }

    public function esSupervisor()
    {
        return $this->role && $this->role->nombre === 'supervisor';
    }

    public function esOperador()
    {
        return $this->role && $this->role->nombre === 'operador';
    }

    public function obtenerEntidadId()
    {
        return $this->operador ? $this->operador->entidad_id : null;
    }
}
