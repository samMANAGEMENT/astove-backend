<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Operadores\models\Operadores;
use App\Http\Modules\servicios\models\Servicios;

class ServiciosRealizados extends Model
{
    use HasFactory;

    protected $table = 'servicios_realizados';

    protected $fillable = [
        'empleado_id',
        'servicio_id',
        'cantidad',
        'fecha'
    ];

// Relación con el modelo Empleado
public function empleado()
{
    return $this->belongsTo(Operadores::class, 'empleado_id');
}

// Relación con el modelo Servicio
public function servicio()
{
    return $this->belongsTo(Servicios::class, 'servicio_id');
}

}

