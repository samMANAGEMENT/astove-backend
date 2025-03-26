<?php

namespace App\Http\Modules\Operadores\models;

use App\Http\Modules\Cargos\models\Cargos;
use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operadores extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'entidad_id',
        'telefono',
        'cargo_id'
    ];

    public function entidades()
    {
        return $this->belongsTo(Entidades::class, 'entidad_id');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargos::class, 'carago_id');
    }
}
