<?php

namespace App\Http\Modules\Inventario\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    use HasFactory;

    protected $table = 'inventario_movimientos';
    
    protected $fillable = [
        'inventario_id',
        'usuario_id',
        'tipo',
        'cantidad_anterior',
        'cantidad_movimiento',
        'cantidad_nueva'
    ];

    protected $casts = [
        'cantidad_anterior' => 'integer',
        'cantidad_movimiento' => 'integer',
        'cantidad_nueva' => 'integer',
    ];

    // Relaciones
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopePorInventario($query, $inventarioId)
    {
        return $query->where('inventario_id', $inventarioId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    // Accessors
    public function getTipoLabelAttribute()
    {
        return $this->tipo === 'entrada' ? 'Entrada' : 'Salida';
    }

    public function getTipoColorAttribute()
    {
        return $this->tipo === 'entrada' ? 'success' : 'danger';
    }
}
