<?php

namespace App\Http\Modules\Productos\Models;

use App\Http\Modules\Categorias\Models\Categorias;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'categoria_id',
        'precio_unitario',
        'costo_unitario',
        'stock'
    ];

    protected $casts = [
        'precio_unitario' => 'float',
        'costo_unitario' => 'float',
        'stock' => 'integer',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }

    public function getGananciaAttribute()
    {
        return $this->costo_unitario - $this->precio_unitario;
    }

    public function getGananciaTotalAttribute()
    {
        return $this->ganancia * $this->stock;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock > 10) {
            return 'success';
        } elseif ($this->stock <= 5) {
            return 'danger';
        } else {
            return 'warning';
        }
    }
}
