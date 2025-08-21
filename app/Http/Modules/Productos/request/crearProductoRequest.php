<?php

namespace App\Http\Modules\Productos\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearProductoRequest extends FormRequest
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
            'categoria_id' => 'required|exists:categorias,id',
            'precio_unitario' => 'required|numeric|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
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
            'nombre.required' => 'El nombre del producto es requerido',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'categoria_id.required' => 'La categoría es requerida',
            'categoria_id.exists' => 'La categoría seleccionada no existe',
            'precio_unitario.required' => 'El precio unitario es requerido',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'precio_unitario.min' => 'El precio unitario no puede ser negativo',
            'costo_unitario.required' => 'El costo unitario es requerido',
            'costo_unitario.numeric' => 'El costo unitario debe ser un número',
            'costo_unitario.min' => 'El costo unitario no puede ser negativo',
            'stock.required' => 'El stock es requerido',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock no puede ser negativo',
            'entidad_id.exists' => 'La entidad seleccionada no existe'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
