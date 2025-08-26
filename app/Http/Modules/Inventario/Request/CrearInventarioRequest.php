<?php

namespace App\Http\Modules\Inventario\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearInventarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'estado' => 'sometimes|in:activo,inactivo,agotado',
            'tamanio_paquete' => 'sometimes|integer|min:1'
        ];

        // Solo los admins pueden especificar entidad_id
        if (auth()->user()->esAdmin()) {
            $rules['entidad_id'] = 'sometimes|exists:entidades,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del artículo es requerido',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad no puede ser negativa',
            'costo_unitario.required' => 'El costo unitario es requerido',
            'costo_unitario.numeric' => 'El costo unitario debe ser un número',
            'costo_unitario.min' => 'El costo unitario no puede ser negativo',
            'estado.in' => 'El estado debe ser activo, inactivo o agotado',
            'entidad_id.exists' => 'La entidad seleccionada no existe',
            'tamanio_paquete.integer' => 'El tamaño del paquete debe ser un número entero',
            'tamanio_paquete.min' => 'El tamaño del paquete debe ser mayor a 0'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
