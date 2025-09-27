<?php

namespace App\Http\Modules\ListaEspera\Request;

use Illuminate\Foundation\Http\FormRequest;

class crearPersonaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'servicio' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'notas' => 'nullable|string',
            'fecha' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es requerido',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres',
            'servicio.required' => 'El servicio es requerido',
            'servicio.string' => 'El servicio debe ser una cadena de texto',
            'servicio.max' => 'El servicio no puede exceder los 255 caracteres',
            'telefono.string' => 'El teléfono debe ser una cadena de texto',
            'telefono.max' => 'El teléfono no puede exceder los 20 caracteres',
            'notas.string' => 'Las notas deben ser una cadena de texto',
            'fecha.required' => 'La fecha es requerida',
            'fecha.date' => 'La fecha debe ser una fecha válida',
        ];
    }
}
