<?php

namespace App\modules\entidades\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class entidades extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
    ];
}
