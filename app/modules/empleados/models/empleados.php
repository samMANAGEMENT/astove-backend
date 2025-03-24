<?php

namespace App\modules\empleados\models;

use App\modules\entidades\models\entidades;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class empleados extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'direccion',
        'entidad_id',
    ];

    public function entidad()
    {
        return $this->belongsTo(entidades::class);
    }
}