<?php

namespace App\Http\Modules\Inventario\Models;

use App\Models\User;
use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Inventario\Models\InventarioMovimiento;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';
    
    protected $fillable = [
        'nombre',
        'cantidad',
        'costo_unitario',
        'estado',
        'entidad_id',
        'creado_por'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'float',
    ];

    // Relaciones
    public function entidad()
    {
        return $this->belongsTo(Entidades::class, 'entidad_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_id');
    }

    // Accessors
    public function getValorTotalAttribute()
    {
        return $this->cantidad * $this->costo_unitario;
    }

    public function getEstadoCalculadoAttribute()
    {
        if ($this->cantidad <= 0) {
            return 'agotado';
        } elseif ($this->estado === 'inactivo') {
            return 'inactivo';
        } else {
            return 'activo';
        }
    }

    public function getEstadoColorAttribute()
    {
        switch ($this->estado_calculado) {
            case 'agotado':
                return 'danger';
            case 'inactivo':
                return 'warning';
            default:
                return 'success';
        }
    }

    // Asegurar que valor_total siempre esté disponible en las respuestas JSON
    protected $appends = ['valor_total'];

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorEntidad($query, $entidadId)
    {
        return $query->where('entidad_id', $entidadId);
    }

    public function scopeAgotado($query)
    {
        return $query->where('cantidad', 0);
    }

    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    // Métodos
    public function actualizarEstado()
    {
        if ($this->cantidad <= 0) {
            $this->estado = 'agotado';
        } elseif ($this->estado === 'agotado') {
            $this->estado = 'activo';
        }
        $this->save();
    }

    public function agregarStock($cantidad)
    {
        $cantidadAnterior = $this->cantidad;
        $this->cantidad += $cantidad;
        $this->save();
        
        // Registrar movimiento
        $this->movimientos()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'entrada',
            'cantidad_anterior' => $cantidadAnterior,
            'cantidad_movimiento' => $cantidad,
            'cantidad_nueva' => $this->cantidad
        ]);
        
        $this->actualizarEstado();
    }

    public function reducirStock($cantidad)
    {
        if ($this->cantidad < $cantidad) {
            throw new \Exception('No hay suficiente stock disponible');
        }
        
        $cantidadAnterior = $this->cantidad;
        $this->cantidad -= $cantidad;
        $this->save();
        
        // Registrar movimiento
        $this->movimientos()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'salida',
            'cantidad_anterior' => $cantidadAnterior,
            'cantidad_movimiento' => $cantidad,
            'cantidad_nueva' => $this->cantidad
        ]);
        
        $this->actualizarEstado();
    }
}
