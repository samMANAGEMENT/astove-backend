<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Operadores\Models\Operadores;
use App\Http\Modules\servicios\models\Servicios;

class ServiciosRealizados extends Model
{
    use HasFactory;

    protected $table = 'servicios_realizados';

    protected $fillable = [
        'empleado_id',
        'servicio_id',
        'cantidad',
        'fecha',
        'pagado',
        'pago_id',
        'metodo_pago',
        'monto_efectivo',
        'monto_transferencia',
        'total_servicio',
        'descuento_porcentaje',
        'monto_descuento',
        'total_con_descuento'
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'monto_efectivo' => 'decimal:2',
        'monto_transferencia' => 'decimal:2',
        'total_servicio' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'total_con_descuento' => 'decimal:2'
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

// Relación con el modelo Pago
public function pago()
{
    return $this->belongsTo(\App\Http\Modules\pagos\models\pagos::class, 'pago_id');
}

}

