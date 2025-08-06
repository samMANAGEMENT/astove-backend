<?php

namespace App\Http\Modules\servicios\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Operadores\models\Operadores;

class IngresosAdicionales extends Model
{
    use HasFactory;

    protected $table = 'ingresos_adicionales';

    protected $fillable = [
        'concepto',
        'monto',
        'metodo_pago',
        'monto_efectivo',
        'monto_transferencia',
        'tipo',
        'categoria',
        'descripcion',
        'empleado_id',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'monto_efectivo' => 'decimal:2',
        'monto_transferencia' => 'decimal:2'
    ];

    // Relación con el modelo Empleado
    public function empleado()
    {
        return $this->belongsTo(Operadores::class, 'empleado_id');
    }
} 