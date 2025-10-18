<?php

namespace App\Http\Modules\Agenda\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'titulo',
        'hora_inicio',
        'hora_fin',
        'dia_semana',
        'fecha',
        'color',
        'notas',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Scope para obtener horarios base (sin fecha específica)
     */
    public function scopeBase($query)
    {
        return $query->whereNull('fecha');
    }

    /**
     * Scope para obtener horarios específicos de una fecha
     */
    public function scopeEspecificos($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    /**
     * Verificar si es un horario base (recurrente)
     */
    public function esBase()
    {
        return is_null($this->fecha);
    }

    /**
     * Verificar si es un horario específico de una fecha
     */
    public function esEspecifico()
    {
        return !is_null($this->fecha);
    }
}
