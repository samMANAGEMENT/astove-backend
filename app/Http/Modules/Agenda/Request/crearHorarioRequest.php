<?php

namespace App\Http\Modules\Agenda\Request;

use Illuminate\Foundation\Http\FormRequest;

class crearHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agenda_id' => 'required|exists:agendas,id',
            'titulo' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'dia_semana' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'color' => 'nullable|string|max:7',
            'notas' => 'nullable|string',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'agenda_id.required' => 'La agenda es requerida',
            'agenda_id.exists' => 'La agenda seleccionada no existe',
            'titulo.required' => 'El título del horario es requerido',
            'titulo.max' => 'El título no puede tener más de 255 caracteres',
            'hora_inicio.required' => 'La hora de inicio es requerida',
            'hora_inicio.date_format' => 'El formato de hora de inicio debe ser HH:MM',
            'hora_fin.required' => 'La hora de fin es requerida',
            'hora_fin.date_format' => 'El formato de hora de fin debe ser HH:MM',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'dia_semana.required' => 'El día de la semana es requerido',
            'dia_semana.in' => 'El día de la semana debe ser válido'
        ];
    }
}
