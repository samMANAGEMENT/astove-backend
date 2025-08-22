<?php

namespace App\Http\Modules\Entidades\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades extends Model
{
    use HasFactory;

    protected $table = 'entidades';

    protected $fillable = [
        'nombre',
        'direccion',
        'estado'
    ];
}
