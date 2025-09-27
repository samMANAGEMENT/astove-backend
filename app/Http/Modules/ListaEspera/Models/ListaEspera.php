<?php

namespace App\Http\Modules\ListaEspera\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaEspera extends Model
{
    use HasFactory;

    protected $table = 'lista_espera';

    protected $fillable = [
        'nombre',
        'servicio',
        'telefono',
        'notas',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Scope para filtrar por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para ordenar por fecha de creación
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
