<?php

namespace App\Http\Modules\Ventas\Models;

use App\Http\Modules\Operadores\Models\Operadores;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    
    protected $fillable = [
        'total',
        'ganancia_total',
        'empleado_id',
        'metodo_pago',
        'monto_efectivo',
        'monto_transferencia',
        'observaciones',
        'fecha'
    ];

    protected $casts = [
        'total' => 'float',
        'ganancia_total' => 'float',
        'monto_efectivo' => 'float',
        'monto_transferencia' => 'float',
        'fecha' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Operadores::class, 'empleado_id');
    }

    public function productos()
    {
        return $this->belongsToMany(
            \App\Http\Modules\Productos\Models\Productos::class,
            'ventas_productos',
            'venta_id',
            'producto_id'
        )->withPivot('cantidad', 'subtotal')->withTimestamps();
    }
}
