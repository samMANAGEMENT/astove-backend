<?php

namespace App\Http\Modules\productos\models;

use App\Http\Modules\categorias\models\categorias;
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

    public function categoria()
    {
        return $this->belongsTo(categorias::class, 'categoria_id');
    }
}