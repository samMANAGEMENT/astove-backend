<?php

namespace App\Http\Modules\Categorias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre'
    ];
}
