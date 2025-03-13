<?php

namespace App\Http\Requests\Pago;
use Illuminate\Foundation\Http\FormRequest;
class StorePagoRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(){
        return [
            'prestamo_id'          => 'required|exists:prestamos,id',
            'numero_cuota'         => 'required|integer|min:1',
            'capital'              => 'required|numeric|min:0',
            'fecha_inicio'         => 'required|date',
            'fecha_pago'           => 'required|date|after_or_equal:fecha_inicio',
            'dias_interes'         => 'required|integer|min:0',
            'tasa_interes_diario'  => 'required|numeric|min:0',
            'monto_interes_pagar'  => 'required|numeric|min:0',
            'monto_capital_pagar'  => 'required|numeric|min:0',
            'saldo_capital'        => 'required|numeric|min:0',
            'monto_capital_mas'    => 'nullable|numeric|min:0',
        ];
    }
}
