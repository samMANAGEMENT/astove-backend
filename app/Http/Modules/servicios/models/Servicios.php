<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Entidades\models\Entidades;

class Servicios extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'estado',
        'porcentaje_pago_empleado',
        'entidad_id'
    ];

    public function entidad()
    {
        return $this->belongsTo(Entidades::class, 'entidad_id');
    }
}
