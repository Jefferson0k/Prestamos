<?php

namespace App\Services;

use Carbon\Carbon;

class InteresCalculatorService
{
    protected int $minDias = 15;
    protected int $maxDias = 30;

    public function calcularDiasParaInteres(int $dias, bool $pagoCompleto = false): int
    {
        if ($pagoCompleto) {
            return $dias > $this->maxDias ? $dias : $this->maxDias;
        }

        return match (true) {
            $dias >= 1 && $dias <= $this->minDias => $this->minDias,
            $dias > $this->minDias && $dias < $this->maxDias => $this->maxDias,
            default => $dias,
        };
    }

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

        return $inicio->diffInDays($fin) + 1;
    }

    public function calcularInteres(
        float $capital,
        float $tasaInteresDiario,
        int $dias,
        bool $aplicarReglas = true,
        bool $pagoCompleto = false,
        float $montoCapitalPagado = null
    ): array {
        $diasCalculados = $aplicarReglas
            ? $this->calcularDiasParaInteres($dias, $pagoCompleto)
            : $dias;

        $tasaInteresDecimal = $tasaInteresDiario / 100;
        $interes = $diasCalculados * $tasaInteresDecimal;

        // Determinar qué regla aplicar basándose en los días reales
        $aplicaRegla15 = $dias <= $this->minDias;
        $aplicaRegla30 = $dias > $this->minDias && $dias < $this->maxDias;

        // Calcular el monto de interés según la regla correspondiente
        if ($aplicaRegla15) {
            // Regla de 15 días: (interes/100) * monto_capital_pagar
            $montoBase = $montoCapitalPagado ?? $capital;
            $montoInteresPagar = ($interes / 100) * $montoBase;
            $reglAplicada = '15_dias';
        } elseif ($aplicaRegla30) {
            // Regla de 30 días: (interes/100) * capital
            $montoInteresPagar = ($interes / 100) * $capital;
            $reglAplicada = '30_dias';
        } else {
            // Más de 30 días: usar días transcurridos con capital completo
            $montoInteresPagar = ($interes / 100) * $capital;
            $reglAplicada = 'dias_transcurridos';
        }

        // Redondear el monto de interés a pagar
        //$montoInteresPagar = round($montoInteresPagar, 2);
        $montoInteresPagar = (float) ceil($montoInteresPagar);

        return [
            'dias' => $dias,
            'dias_calculados' => $diasCalculados,
            'tasa_interes_diario' => $tasaInteresDiario,
            'tasa_interes_decimal' => $tasaInteresDecimal,
            'interes' => $interes,
            'monto_interes_pagar' => $montoInteresPagar,
            'regla_aplicada' => $reglAplicada,
            'aplica_regla_15' => $aplicaRegla15,
            'aplica_regla_30' => $aplicaRegla30,
            'interes_reducido' => $aplicaRegla15 && $montoCapitalPagado < $capital
        ];
    }

    public function calcularPago(
        float $capital,
        float $montoCapitalPagado,
        array $datosInteres
    ): array {
        $saldoCapital = $capital - $montoCapitalPagado;
        $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagado;
        
        // Redondear el monto total a pagar
        $montoTotalPagar = round($montoTotalPagar, 2);
        
        $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';

        return [
            'capital' => $capital,
            'monto_capital_pagar' => $montoCapitalPagado,
            'saldo_capital' => $saldoCapital,
            'monto_interes_pagar' => $datosInteres['monto_interes_pagar'],
            'monto_total_pagar' => $montoTotalPagar,
            'estado' => $estado,
            'interes_reducido' => $datosInteres['interes_reducido'] ?? false,
            'regla_aplicada' => $datosInteres['regla_aplicada'] ?? null
        ];
    }

    public function determinarEstado(string $estadoActual, int $dias): string
    {
        return $dias > $this->maxDias ? 'Vencido' : $estadoActual;
    }

    /**
     * Método auxiliar para entender qué regla se aplicará
     */
    public function explicarRegla(int $dias): array
    {
        return [
            'dias' => $dias,
            'regla' => match (true) {
                $dias <= $this->minDias => "Regla 15 días: Se cobra interés sobre el monto pagado",
                $dias > $this->minDias && $dias < $this->maxDias => "Regla 30 días: Se cobra interés sobre el capital total",
                default => "Días transcurridos: Se cobra interés sobre el capital total por días reales"
            },
            'formula' => match (true) {
                $dias <= $this->minDias => "(interes/100) * monto_capital_pagar",
                $dias > $this->minDias && $dias < $this->maxDias => "(interes/100) * capital",
                default => "(interes/100) * capital"
            }
        ];
    }
}