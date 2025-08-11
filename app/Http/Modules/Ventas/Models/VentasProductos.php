<?php

namespace App\Http\Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VentasProductos extends Pivot
{
    protected $table = 'ventas_productos';
    
    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'subtotal'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'subtotal' => 'float',
    ];
}
