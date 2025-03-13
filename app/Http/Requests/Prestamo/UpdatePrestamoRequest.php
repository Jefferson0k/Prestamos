<?php

namespace App\Http\Requests\Prestamo;
use Illuminate\Foundation\Http\FormRequest;
class UpdatePrestamoRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(){
        return [
            'fecha_inicio'       => 'sometimes|date',
            'fecha_vencimiento'  => 'sometimes|date|after_or_equal:fecha_inicio',
            'capital'            => 'sometimes|numeric|min:0',
            'numero_cuotas'      => 'sometimes|integer|min:1',
            'estado_cliente'     => 'sometimes|string|max:255',
            'recomendacion'      => 'sometimes|string|max:255',
            'tasa_interes_diario'=> 'sometimes|numeric|min:0',
        ];
    }
}
