<?php

namespace App\Http\Modules\categorias\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class categorias extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre'
    ];
}
