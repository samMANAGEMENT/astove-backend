<?php

namespace App\Http\Modules\pagos\models;

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

}
