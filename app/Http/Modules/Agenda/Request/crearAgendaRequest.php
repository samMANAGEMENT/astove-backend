<?php

namespace App\Http\Modules\Agenda\Request;

use Illuminate\Foundation\Http\FormRequest;

class crearAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'operador_id' => 'required|exists:operadores,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'operador_id.required' => 'El operador es requerido',
            'operador_id.exists' => 'El operador seleccionado no existe',
            'nombre.required' => 'El nombre de la agenda es requerido',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres'
        ];
    }
}
