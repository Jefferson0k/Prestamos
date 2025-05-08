<?php
namespace App\Services;

use App\Models\Cuotas;
use Carbon\Carbon;

class PagoService {
    public function registrarPago($cuotaId, $montoCapitalPagado) {
        $cuota = Cuotas::findOrFail($cuotaId);
        $prestamo = $cuota->prestamo;
        $hoy = Carbon::now()->startOfDay();

        $fechaInicio = $cuota->Fecha_Inicio ? Carbon::parse($cuota->Fecha_Inicio)->startOfDay() : null;
        if (!$fechaInicio) {
            throw new \Exception("La cuota aún no está activa. No tiene Fecha_Inicio.");
        }

        $dias = $fechaInicio->diffInDays($hoy);
        $capital = floatval($cuota->capital ?? 0);
        $tasaInteresDiarioEntero = floatval($cuota->Tasa_Interes_Diario ?? 0);
        $tasaInteresDecimal = $tasaInteresDiarioEntero / 100;
        $interes = $dias * $tasaInteresDecimal;
        $montoInteresPagar = ($interes / 100) * $capital;

        $montoCapitalPagado = floatval($montoCapitalPagado);
        
        // Validar que el monto pagado no exceda el capital
        if ($montoCapitalPagado > $capital) {
            throw new \Exception("El monto de pago excede el capital pendiente. No se puede pagar más de lo que se debe.");
        }

        $saldoCapital = $capital - $montoCapitalPagado;
        $montoTotalPagar = $montoInteresPagar + $montoCapitalPagado;
        $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';

        $cuota->update([
            'fecha_vencimiento' => $hoy,
            'Dias' => $dias,
            'interes' => $interes,
            'Monto_Interes_Pagar' => $montoInteresPagar,
            'Monto_Capital_Pagar' => $montoCapitalPagado,
            'Saldo_Capital' => $saldoCapital,
            'MOnto_Capital_Mas_Interes_a_Pagar' => $montoTotalPagar,
            'estado' => $estado
        ]);

        // Verificar si el pago cubre todo el capital
        if ($montoCapitalPagado >= $capital) {
            // Si se pagó todo el capital, se marcan las cuotas siguientes como "Cancelado"
            Cuotas::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', '>', $cuota->numero_cuota)
                ->update([
                    'estado' => 'Cancelado',
                    'Fecha_Inicio' => null,
                    'capital' => 0,
                    'Saldo_Capital' => 0,
                ]);
        } else {
            // Si queda saldo pendiente, actualizar las cuotas futuras con el nuevo capital
            $siguienteNumero = $cuota->numero_cuota + 1;
            $cuotasPendientes = Cuotas::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', '>=', $siguienteNumero)
                ->where('estado', 'Pendiente')
                ->orderBy('numero_cuota')
                ->get();

            foreach ($cuotasPendientes as $index => $cuotaPendiente) {
                if ($index === 0) {
                    $cuotaPendiente->update([
                        'capital' => $saldoCapital,
                        'Fecha_Inicio' => $hoy,
                        'Saldo_Capital' => $saldoCapital,
                    ]);
                } else {
                    $cuotaAnterior = $cuotasPendientes[$index - 1];
                    $nuevoCapital = $cuotaAnterior->capital;
                    $cuotaPendiente->update([
                        'capital' => $nuevoCapital,
                        'Saldo_Capital' => $nuevoCapital,
                    ]);
                }
            }

            // Si no existen cuotas pendientes, se crea una nueva
            if ($cuotasPendientes->isEmpty()) {
                Cuotas::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $siguienteNumero,
                    'capital' => $saldoCapital,
                    'Fecha_Inicio' => $hoy,
                    'fecha_vencimiento' => null,
                    'Tasa_Interes_Diario' => $cuota->Tasa_Interes_Diario,
                    'Saldo_Capital' => $saldoCapital,
                    'estado' => 'Pendiente'
                ]);
            }
        }

        return true;
    }
}
