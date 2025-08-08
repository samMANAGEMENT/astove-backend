<?php

namespace App\Http\Modules\Ventas\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
            'productoId' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'metodoPago' => 'required|in:efectivo,transferencia,mixto',
            'montoEfectivo' => 'nullable|numeric|min:0',
            'montoTransferencia' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'productoId.required' => 'El producto es requerido',
            'productoId.exists' => 'El producto seleccionado no existe',
            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'metodoPago.required' => 'El método de pago es requerido',
            'metodoPago.in' => 'El método de pago debe ser efectivo, transferencia o mixto',
            'montoEfectivo.numeric' => 'El monto en efectivo debe ser un número',
            'montoEfectivo.min' => 'El monto en efectivo no puede ser negativo',
            'montoTransferencia.numeric' => 'El monto en transferencia debe ser un número',
            'montoTransferencia.min' => 'El monto en transferencia no puede ser negativo',
            'observaciones.max' => 'Las observaciones no pueden exceder los 500 caracteres'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
