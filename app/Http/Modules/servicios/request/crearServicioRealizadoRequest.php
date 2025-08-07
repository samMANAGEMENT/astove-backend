<?php

namespace App\Http\Modules\servicios\request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class crearServicioRealizadoRequest extends FormRequest
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
        return [
            'empleado_id' => 'required|integer|exists:operadores,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_transferencia' => 'required|numeric|min:0',
            'total_servicio' => 'required|numeric|min:0',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'empleado_id.required' => 'El empleado es requerido',
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            'servicio_id.required' => 'El servicio es requerido',
            'servicio_id.exists' => 'El servicio seleccionado no existe',
            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'fecha.required' => 'La fecha es requerida',
            'fecha.date' => 'La fecha debe tener un formato válido',
            'fecha.date' => 'La fecha debe tener un formato válido',
            'metodo_pago.required' => 'El método de pago es requerido',
            'metodo_pago.in' => 'El método de pago debe ser efectivo o transferencia',
            'monto_efectivo.required' => 'El monto en efectivo es requerido',
            'monto_efectivo.min' => 'El monto en efectivo debe ser mayor o igual a 0',
            'monto_transferencia.required' => 'El monto en transferencia es requerido',
            'monto_transferencia.min' => 'El monto en transferencia debe ser mayor o igual a 0',
            'total_servicio.required' => 'El total del servicio es requerido',
            'total_servicio.min' => 'El total del servicio debe ser mayor a 0',
            'descuento_porcentaje.min' => 'El descuento debe ser mayor o igual a 0',
            'descuento_porcentaje.max' => 'El descuento no puede ser mayor al 100%'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
} 