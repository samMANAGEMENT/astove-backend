<?php

namespace App\Http\Modules\pagos\models;

use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pagos extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'monto',
        'fecha',
        'estado'
    ];

    public function empleado()
    {
        return $this->belongsTo(Operadores::class, 'empleados_id');
    }
}
