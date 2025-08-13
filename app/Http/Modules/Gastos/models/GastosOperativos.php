<?php

namespace App\Http\Modules\Gastos\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastosOperativos extends Model
{
    protected $table = 'gastos_operativos';
    
    protected $fillable = [
        'entidad_id',
        'descripcion',
        'monto',
        'fecha'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime:Y-m-d H:i:s'
    ];

    public function entidad(): BelongsTo
    {
        return $this->belongsTo(\App\Http\Modules\Entidades\models\Entidades::class, 'entidad_id');
    }


}
