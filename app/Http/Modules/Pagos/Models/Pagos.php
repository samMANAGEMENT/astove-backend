<?php

namespace App\Http\Modules\Pagos\Models;

use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'monto',
        'fecha',
        'estado',
        'tipo_pago',
        'monto_pendiente_antes',
        'monto_pendiente_despues',
        'servicios_incluidos',
        'semana_pago'
    ];

    protected $casts = [
        'servicios_incluidos' => 'array',
        'fecha' => 'datetime',
        'estado' => 'boolean'
    ];

    public function empleado()
    {
        return $this->belongsTo(Operadores::class, 'empleado_id');
    }
}
