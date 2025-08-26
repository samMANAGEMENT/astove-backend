<?php

namespace App\Http\Modules\Agenda\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'horario_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'servicio',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado', // 'confirmada', 'pendiente', 'cancelada', 'completada'
        'notas',
        'created_by'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
