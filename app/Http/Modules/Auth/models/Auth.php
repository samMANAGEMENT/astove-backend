<?php

namespace App\Http\Modules\Auth\models;

use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Auth extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'users';
    protected $fillable = [
        'email',
        'password',
        'operador_id'
    ];

    public function operador()
    {
        return $this->belongsTo(Operadores::class, 'afiliado_id');
    }
}
