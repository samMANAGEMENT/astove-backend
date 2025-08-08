<?php

namespace App\Http\Modules\Ventas\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearVentaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:efectivo,transferencia,mixto',
            'monto_efectivo' => 'required_if:metodo_pago,efectivo,mixto|numeric|min:0',
            'monto_transferencia' => 'required_if:metodo_pago,transferencia,mixto|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'producto_id.required' => 'El producto es requerido',
            'producto_id.exists' => 'El producto seleccionado no existe',
            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'metodo_pago.required' => 'El método de pago es requerido',
            'metodo_pago.in' => 'El método de pago debe ser efectivo, transferencia o mixto',
            'monto_efectivo.required_if' => 'El monto en efectivo es requerido',
            'monto_efectivo.numeric' => 'El monto en efectivo debe ser un número',
            'monto_efectivo.min' => 'El monto en efectivo no puede ser negativo',
            'monto_transferencia.required_if' => 'El monto en transferencia es requerido',
            'monto_transferencia.numeric' => 'El monto en transferencia debe ser un número',
            'monto_transferencia.min' => 'El monto en transferencia no puede ser negativo',
            'observaciones.string' => 'Las observaciones deben ser texto',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
