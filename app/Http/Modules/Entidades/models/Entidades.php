<?php

namespace App\Http\Modules\Entidades\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'direccion',
        'estado'
    ];
}
