<?php

namespace App\Http\Modules\Agenda\Models;

use App\Http\Modules\Operadores\Models\Operadores;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'operador_id',
        'nombre',
        'descripcion',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean'
    ];

    public function operador()
    {
        return $this->belongsTo(Operadores::class, 'operador_id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function horariosActivos()
    {
        return $this->hasMany(Horario::class)->where('activo', true);
    }
}
