<?php

namespace App\Http\Modules\productos\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productos extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria_id',
        'precio_unitario',
        'costo_unitario',
        'stock'
    ];
}