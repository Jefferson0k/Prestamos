<?php

namespace App\Services;

use Carbon\Carbon;

class InteresCalculatorService
{
    /**
     * Calcula los días para el interés según las reglas de negocio
     * 
     * @param int $dias Número real de días transcurridos
     * @param bool $pagoCompleto Indica si se está pagando el capital completo
     * @return int Días ajustados para cálculo de interés
     */
    public function calcularDiasParaInteres(int $dias, bool $pagoCompleto = false): int
    {
        // Si es pago completo, tomamos 30 días (a menos que hayan transcurrido más)
        if ($pagoCompleto) {
            return $dias > 30 ? $dias : 30;
        }
        
        // Reglas originales para pagos parciales
        if ($dias >= 1 && $dias < 15) {
            return 15;
        }
        if ($dias == 15) {
            return 15;
        }
        if ($dias >= 16 && $dias < 30) {
            return 30;
        }
        return $dias;
    }

    /**
     * Calcula los días transcurridos entre la fecha de inicio y hoy
     * 
     * @param string|Carbon|null $fechaInicio Fecha de inicio
     * @param Carbon|null $fechaFin Fecha final (default: hoy)
     * @return int Número de días
     */
    public function calcularDias($fechaInicio, $fechaFin = null): int
    {
        if (!$fechaInicio) {
            return 0;
        }

        $inicio = $fechaInicio instanceof Carbon 
            ? $fechaInicio->copy()->startOfDay() 
            : Carbon::parse($fechaInicio)->startOfDay();
            
        $fin = $fechaFin instanceof Carbon 
            ? $fechaFin->copy()->startOfDay() 
            : Carbon::now()->startOfDay();

        // Siempre incluimos el día actual (+1) como en CuotaResource
        return $inicio->diffInDays($fin) + 1;
    }

    /**
     * Calcula el monto de interés a pagar
     * 
     * @param float $capital Capital pendiente
     * @param float $tasaInteresDiario Tasa de interés diario (valor entero)
     * @param int $dias Días transcurridos
     * @param bool $aplicarReglas Si se aplican reglas de negocio a los días
     * @param bool $pagoCompleto Indica si se está pagando el capital completo
     * @return array Información del cálculo de interés
     */
    public function calcularInteres(float $capital, float $tasaInteresDiario, int $dias, bool $aplicarReglas = true, bool $pagoCompleto = false): array
    {
        // Siguiendo la lógica exacta de CuotaResource, pero añadiendo el parámetro pagoCompleto
        $diasCalculados = $aplicarReglas ? $this->calcularDiasParaInteres($dias, $pagoCompleto) : $dias;
        $tasaInteresDecimal = $tasaInteresDiario / 100;
        $interes = $diasCalculados * $tasaInteresDecimal;
        $montoInteresPagar = ($interes / 100) * $capital;

        return [
            'dias' => $dias,
            'dias_calculados' => $diasCalculados,
            'tasa_interes_diario' => $tasaInteresDiario,
            'tasa_interes_decimal' => $tasaInteresDecimal,
            'interes' => $interes,
            'monto_interes_pagar' => $montoInteresPagar
        ];
    }

    /**
     * Calcula información completa de pago
     * 
     * @param float $capital Capital pendiente
     * @param float $montoCapitalPagado Monto de capital que se pagará
     * @param array $datosInteres Datos del cálculo de interés
     * @return array Información del cálculo de pago
     */
    public function calcularPago(float $capital, float $montoCapitalPagado, array $datosInteres): array
    {
        $saldoCapital = $capital - $montoCapitalPagado;
        $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagado;
        $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';

        return [
            'capital' => $capital,
            'monto_capital_pagar' => $montoCapitalPagado,
            'saldo_capital' => $saldoCapital,
            'monto_interes_pagar' => $datosInteres['monto_interes_pagar'],
            'monto_total_pagar' => $montoTotalPagar,
            'estado' => $estado
        ];
    }

    /**
     * Determina el estado basado en los días transcurridos
     * 
     * @param string $estadoActual Estado actual de la cuota
     * @param int $dias Días transcurridos
     * @return string Estado actualizado
     */
    public function determinarEstado(string $estadoActual, int $dias): string
    {
        if ($dias > 30) {
            return 'Vencido';
        }
        
        return $estadoActual;
    }
}