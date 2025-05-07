<?php

namespace App\Http\Requests\Prestamo;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrestamoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_inicio' => 'required|before_or_equal:fecha_vencimiento',
            'fecha_vencimiento' => 'required|after_or_equal:fecha_inicio',
            'capital' => 'required|numeric|min:1',
            'numero_cuotas' => 'required|integer|min:1',
            'estado_cliente' => 'required|integer',
            'recomendado_id' => 'required|exists:clientes,id',
            'tasa_interes_diario' => 'required|numeric|min:0',
        ];
        
    }
    public function messages(){
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe en el sistema.',

            'fecha_inicio.required' => 'La fecha de inicio del préstamo es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',

            'fecha_vencimiento.required' => 'La fecha de vencimiento del préstamo es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de inicio.',

            'capital.required' => 'El monto del capital es obligatorio.',
            'capital.numeric' => 'El capital debe ser un valor numérico.',
            'capital.min' => 'El capital debe ser mayor o igual a 0.',

            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'numero_cuotas.min' => 'El número de cuotas debe ser al menos 1.',

            'estado_cliente.integer' => 'El estado del cliente debe ser numerico.',
            'estado_cliente.required' => 'El estado del cliente es obligatorio.',

            'recomendado_id.required' => 'El cliente es obligatorio.',
            'recomendado_id.exists' => 'El cliente seleccionado no existe en el sistema.',

            'tasa_interes_diario.required' => 'La tasa de interés diario es obligatoria.',
            'tasa_interes_diario.numeric' => 'La tasa de interés diario debe ser un valor numérico.',
            'tasa_interes_diario.min' => 'La tasa de interés diario debe ser mayor o igual a 0.'
        ];
    }
}
