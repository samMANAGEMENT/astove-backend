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
}
