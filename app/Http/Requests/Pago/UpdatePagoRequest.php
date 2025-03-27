<?php

namespace App\Http\Requests\Pago;
use Illuminate\Foundation\Http\FormRequest;
class UpdatePagoRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(){
        return [
            'fecha_pago'           => 'sometimes|date|after_or_equal:fecha_inicio',
            'dias_interes'         => 'sometimes|integer|min:0',
            'tasa_interes_diario'  => 'sometimes|numeric|min:0',
            'monto_interes_pagar'  => 'sometimes|numeric|min:0',
            'monto_capital_pagar'  => 'sometimes|numeric|min:0',
            'saldo_capital'        => 'sometimes|numeric|min:0',
            'monto_capital_mas'    => 'sometimes|numeric|min:0',
        ];
    }
}
