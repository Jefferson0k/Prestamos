<?php
namespace App\Services;

use App\Models\Cuotas;
use App\Models\Pagos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PagoService
{
    public function registrarPago($cuotaId, $montoCapitalPagado)
    {
        try {
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
            if ($montoCapitalPagado > $capital) {
                throw new \Exception("El monto de pago excede el capital pendiente.");
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

            Pagos::create([
                'prestamo_id' => $prestamo->id,
                'cuota_id' => $cuota->id,
                'capital' => $capital,
                'fecha_pago' => $hoy,
                'monto_capital' => $montoCapitalPagado,
                'monto_interes' => $montoInteresPagar,
                'monto_total' => $montoTotalPagar,
            ]);

            if ($montoCapitalPagado >= $capital) {
                Cuotas::where('prestamo_id', $prestamo->id)
                    ->where('numero_cuota', '>', $cuota->numero_cuota)
                    ->update([
                        'estado' => 'Cancelado',
                        'Fecha_Inicio' => null,
                        'capital' => 0,
                        'Saldo_Capital' => 0,
                    ]);
            } else {
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

                if ($cuotasPendientes->isEmpty() && $saldoCapital > 0) {
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

            $this->actualizarEstadoPrestamo($prestamo->id);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al registrar pago: " . $e->getMessage());
            throw $e;
        }
    }
    private function actualizarEstadoPrestamo($prestamoId){
        try {
            $prestamo = Cuotas::where('prestamo_id', $prestamoId)->first()->prestamo;
            $cuotasConSaldo = Cuotas::where('prestamo_id', $prestamoId)
                ->where('Saldo_Capital', '>', 0)
                ->count();

            Log::info("Verificando estado préstamo {$prestamoId}: Cuotas con saldo > 0 = {$cuotasConSaldo}");

            if ($cuotasConSaldo === 0) {
                // Marcar cuotas 'Parcial' con saldo 0 como 'Pagado'
                Cuotas::where('prestamo_id', $prestamoId)
                    ->where('estado', 'Parcial')
                    ->where('Saldo_Capital', '<=', 0)
                    ->update(['estado' => 'Pagado']);

                // Marcar préstamo como pagado
                $prestamo->update([
                    'estado_cliente' => 4
                ]);

                Log::info("Préstamo {$prestamoId} marcado como pagado completamente (estado_cliente=4)");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error al actualizar estado del préstamo: " . $e->getMessage());
            return false;
        }
    }

}
