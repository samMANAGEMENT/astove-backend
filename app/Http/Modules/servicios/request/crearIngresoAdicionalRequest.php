<?php

namespace App\Http\Modules\servicios\Request;

use Illuminate\Foundation\Http\FormRequest;

class crearIngresoAdicionalRequest extends FormRequest
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
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia,mixto',
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_transferencia' => 'required|numeric|min:0',
            'tipo' => 'required|in:accesorio,servicio_ocasional,otro',
            'categoria' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'empleado_id' => 'nullable|exists:operadores,id',
            'fecha' => 'required|date'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'concepto.required' => 'El concepto es obligatorio',
            'concepto.max' => 'El concepto no puede tener más de 255 caracteres',
            'monto.required' => 'El monto es obligatorio',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'El método de pago es obligatorio',
            'metodo_pago.in' => 'El método de pago debe ser efectivo, transferencia o mixto',
            'monto_efectivo.required' => 'El monto en efectivo es obligatorio',
            'monto_efectivo.numeric' => 'El monto en efectivo debe ser un número',
            'monto_efectivo.min' => 'El monto en efectivo debe ser mayor o igual a 0',
            'monto_transferencia.required' => 'El monto en transferencia es obligatorio',
            'monto_transferencia.numeric' => 'El monto en transferencia debe ser un número',
            'monto_transferencia.min' => 'El monto en transferencia debe ser mayor o igual a 0',
            'tipo.required' => 'El tipo es obligatorio',
            'tipo.in' => 'El tipo debe ser accesorio, servicio_ocasional u otro',
            'categoria.max' => 'La categoría no puede tener más de 255 caracteres',
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe tener un formato válido'
        ];
    }
} 