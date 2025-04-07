<?php
namespace App\Http\Modules\categorias\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class categorias extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['nombre'];
    protected $dates = ['deleted_at'];
}
