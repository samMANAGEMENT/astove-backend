<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Operadores\Models\Operadores;
use App\Http\Modules\servicios\models\Servicios;
use App\Traits\PostgresBooleanTrait;
use Illuminate\Support\Facades\DB;

class ServiciosRealizados extends Model
{
    use HasFactory, PostgresBooleanTrait;

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
        'total_con_descuento' => 'decimal:2',
        'fecha' => 'datetime'
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
    return $this->belongsTo(\App\Http\Modules\Pagos\Models\Pagos::class, 'pago_id');
}

/**
 * Actualizar el estado de pagado usando SQL raw para PostgreSQL
 */
public static function updatePagado($empleadoId, $pagado = true, $pagoId = null)
{
    $pagadoValue = $pagado ? DB::raw('TRUE') : DB::raw('FALSE');
    
    $query = self::where('empleado_id', $empleadoId)
                ->whereRaw('pagado IS FALSE');
    
    return $query->update([
        'pagado' => $pagadoValue,
        'pago_id' => $pagoId
    ]);
}

/**
 * Actualizar servicios específicos como pagados
 */
public static function updateServiciosPagados($serviciosIds, $pagoId)
{
    return self::whereIn('id', $serviciosIds)->update([
        'pagado' => DB::raw('TRUE'),
        'pago_id' => $pagoId
    ]);
}

}