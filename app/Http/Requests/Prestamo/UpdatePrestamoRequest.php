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
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'capital' => 'required|numeric|min:1',
            'numero_cuotas' => 'required|integer|min:1',
            'estado_cliente' => 'required|in:Paga,Moroso',
            'recomendacion' => 'nullable|string',
            'tasa_interes_diario' => 'required|numeric|min:0',
        ];
    }
}
