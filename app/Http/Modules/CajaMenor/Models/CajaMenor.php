<?php

namespace App\Http\Modules\CajaMenor\Models;

use App\Http\Modules\Entidades\models\Entidades;
use App\Http\Modules\Operadores\Models\Operadores;
use App\Http\Modules\servicios\models\Servicios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaMenor extends Model
{
    use HasFactory;

    protected $table = 'caja_menor';

    protected $fillable = [
        'entidad_id',
        'operador_id',
        'servicio_id',
        'monto',
        'metodo_pago',
        'monto_efectivo',
        'monto_transferencia',
        'fecha',
        'observaciones',
    ];

    public function entidad()
    {
        return $this->belongsTo(Entidades::class, 'entidad_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Operadores::class, 'operador_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicios::class, 'servicio_id');
    }
}
