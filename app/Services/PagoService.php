<?php
namespace App\Services;

use App\Models\Cuotas;
use App\Models\Pagos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
                'dias' => $dias,
                'interes' => $interes,
                'monto_interes_pagar' => $montoInteresPagar,
                'monto_capital_pagar' => $montoCapitalPagado,
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes_a_pagar' => $montoTotalPagar,
                'estado' => $estado,
                'usuario_id' => Auth::id(),
            ]);

            Pagos::create([
                'prestamo_id' => $prestamo->id,
                'cuota_id' => $cuota->id,
                'capital' => $capital,
                'fecha_pago' => $hoy,
                'monto_capital' => $montoCapitalPagado,
                'monto_interes' => $montoInteresPagar,
                'monto_total' => $montoTotalPagar,
                'usuario_id' => Auth::id(),
            ]);

            if ($montoCapitalPagado >= $capital) {
                Cuotas::where('prestamo_id', $prestamo->id)
                    ->where('numero_cuota', '>', $cuota->numero_cuota)
                    ->update([
                        'estado' => 'Cancelado',
                        'fecha_inicio' => null,
                        'capital' => 0,
                        'saldo_capital' => 0,
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
                            'fecha_inicio' => $hoy,
                            'saldo_capital' => $saldoCapital,
                        ]);
                    } else {
                        $cuotaAnterior = $cuotasPendientes[$index - 1];
                        $nuevoCapital = $cuotaAnterior->capital;
                        $cuotaPendiente->update([
                            'capital' => $nuevoCapital,
                            'saldo_capital' => $nuevoCapital,
                        ]);
                    }
                }

                if ($cuotasPendientes->isEmpty() && $saldoCapital > 0) {
                    Cuotas::create([
                        'prestamo_id' => $prestamo->id,
                        'numero_cuota' => $siguienteNumero,
                        'capital' => $saldoCapital,
                        'fecha_inicio' => $hoy,
                        'fecha_vencimiento' => null,
                        'tasa_interes_diario' => $cuota->Tasa_Interes_Diario,
                        'saldo_capital' => $saldoCapital,
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
                ->where('saldo_capital', '>', 0)
                ->count();

            Log::info("Verificando estado préstamo {$prestamoId}: Cuotas con saldo > 0 = {$cuotasConSaldo}");

            if ($cuotasConSaldo === 0) {
                // Marcar cuotas 'Parcial' con saldo 0 como 'Pagado'
                Cuotas::where('prestamo_id', $prestamoId)
                    ->where('estado', 'Parcial')
                    ->where('saldo_capital', '<=', 0)
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
