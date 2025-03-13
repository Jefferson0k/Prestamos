<?php

namespace App\Http\Requests\Prestamo;
use Illuminate\Foundation\Http\FormRequest;
class StorePrestamoRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(){
        return [
            'cliente_id'         => 'required|exists:clientes,id',
            'fecha_inicio'       => 'required|date',
            'fecha_vencimiento'  => 'required|date|after_or_equal:fecha_inicio',
            'capital'            => 'required|numeric|min:0',
            'numero_cuotas'      => 'required|integer|min:1',
            'estado_cliente'     => 'nullable|string|max:255',
            'recomendacion'      => 'nullable|string|max:255',
            'tasa_interes_diario'=> 'required|numeric|min:0',
        ];
    }
}
