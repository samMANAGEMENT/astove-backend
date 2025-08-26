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
        'creado_por',
        'tamanio_paquete'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'float',
        'tamanio_paquete' => 'integer',
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
        if ($this->tiene_paquetes && $this->tamanio_paquete) {
            // Si tiene paquetes, el costo_unitario ya es el costo del paquete completo
            // Calcular: número de paquetes × costo por paquete
            return $this->cantidad * $this->costo_unitario;
        }
        
        // Si no tiene paquetes, calcular: cantidad × costo unitario
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
    protected $appends = ['valor_total', 'tiene_paquetes', 'informacion_paquetes'];

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
        // Si tiene paquetes, convertir unidades a paquetes
        if ($this->tiene_paquetes && $this->tamanio_paquete) {
            $numeroPaquetes = $cantidad / $this->tamanio_paquete;
            if ($numeroPaquetes != floor($numeroPaquetes)) {
                throw new \Exception('La cantidad debe ser múltiplo del tamaño del paquete');
            }
            $this->agregarPaquetes($numeroPaquetes);
            return;
        }
        
        // Lógica original para artículos unitarios
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
        // Si tiene paquetes, convertir unidades a paquetes
        if ($this->tiene_paquetes && $this->tamanio_paquete) {
            $numeroPaquetes = $cantidad / $this->tamanio_paquete;
            if ($numeroPaquetes != floor($numeroPaquetes)) {
                throw new \Exception('La cantidad debe ser múltiplo del tamaño del paquete');
            }
            $this->reducirPaquetes($numeroPaquetes);
            return;
        }
        
        // Lógica original para artículos unitarios
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

    // Métodos para manejo de paquetes
    public function agregarPaquetes($numeroPaquetes)
    {
        if (!$this->tamanio_paquete) {
            throw new \Exception('Este artículo no tiene configurado un tamaño de paquete');
        }
        
        // En la nueva lógica, cantidad = número de paquetes
        // Simplemente sumar el número de paquetes
        $cantidadAnterior = $this->cantidad;
        $this->cantidad += $numeroPaquetes;
        $this->save();
        
        // Registrar movimiento con la cantidad de unidades agregadas
        $unidadesAgregadas = $numeroPaquetes * $this->tamanio_paquete;
        $this->movimientos()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'entrada',
            'cantidad_anterior' => $cantidadAnterior * $this->tamanio_paquete,
            'cantidad_movimiento' => $unidadesAgregadas,
            'cantidad_nueva' => $this->cantidad * $this->tamanio_paquete
        ]);
        
        $this->actualizarEstado();
    }

    public function reducirPaquetes($numeroPaquetes)
    {
        if (!$this->tamanio_paquete) {
            throw new \Exception('Este artículo no tiene configurado un tamaño de paquete');
        }
        
        if ($this->cantidad < $numeroPaquetes) {
            throw new \Exception('No hay suficientes paquetes disponibles');
        }
        
        // En la nueva lógica, cantidad = número de paquetes
        // Simplemente restar el número de paquetes
        $cantidadAnterior = $this->cantidad;
        $this->cantidad -= $numeroPaquetes;
        $this->save();
        
        // Registrar movimiento con la cantidad de unidades reducidas
        $unidadesReducidas = $numeroPaquetes * $this->tamanio_paquete;
        $this->movimientos()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'salida',
            'cantidad_anterior' => $cantidadAnterior * $this->tamanio_paquete,
            'cantidad_movimiento' => $unidadesReducidas,
            'cantidad_nueva' => $this->cantidad * $this->tamanio_paquete
        ]);
        
        $this->actualizarEstado();
    }

    public function getNumeroPaquetesDisponibles()
    {
        if (!$this->tamanio_paquete) {
            return 0;
        }
        
        // Si tiene paquetes configurados, la cantidad representa el número de paquetes
        return $this->cantidad;
    }

    public function getCantidadSuelta()
    {
        if (!$this->tamanio_paquete) {
            return $this->cantidad;
        }
        
        // Si tiene paquetes configurados, no hay unidades sueltas
        return 0;
    }

    public function getCostoPorPaquete()
    {
        if (!$this->tamanio_paquete) {
            return $this->costo_unitario;
        }
        
        // Si tiene paquetes, el costo_unitario ya es el costo del paquete completo
        return $this->costo_unitario;
    }

    // Accessors adicionales para paquetes
    public function getTienePaquetesAttribute()
    {
        return !is_null($this->tamanio_paquete) && $this->tamanio_paquete > 1;
    }

    public function getInformacionPaquetesAttribute()
    {
        if (!$this->tiene_paquetes) {
            return null;
        }
        
        return [
            'tamanio_paquete' => $this->tamanio_paquete,
            'numero_paquetes' => $this->getNumeroPaquetesDisponibles(),
            'cantidad_suelta' => $this->getCantidadSuelta(),
            'costo_por_paquete' => $this->getCostoPorPaquete()
        ];
    }
}
