<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicios extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'estado',
        'porcentaje_pago_empleado'
    ];
}
