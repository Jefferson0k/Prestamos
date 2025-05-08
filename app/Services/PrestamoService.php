<?php
namespace App\Services;

use App\Models\Cuotas;
use App\Models\Prestamos;
use Carbon\Carbon;

class PrestamoService{
    public function crearPrestamo(array $data){
        $prestamo = Prestamos::create([
            'cliente_id' => $data['cliente_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'capital' => $data['capital'],
            'numero_cuotas' => $data['numero_cuotas'],
            'estado_cliente' => $data['estado_cliente'] ?? 1,
            'recomendado_id' => $data['recomendado_id'],
            'tasa_interes_diario' => $data['tasa_interes_diario'],
            'monto_total' => $data['capital'],
        ]);

        $this->generarCuotas($prestamo);
        return $prestamo;
    }
    public function generarCuotas(Prestamos $prestamo){
        $fechaInicio = Carbon::parse($prestamo->fecha_inicio);
        $capital = $prestamo->capital;
        for ($i = 1; $i <= $prestamo->numero_cuotas; $i++) {
            Cuotas::create([
                'prestamo_id' => $prestamo->id,
                'numero_cuota' => $i,
                'capital' => $capital,
                'interes' => 0.00,
                'Dias' => 0,
                'Tasa_Interes_Diario' => $prestamo->tasa_interes_diario,
                'Monto_Interes_Pagar' => 0.00,
                'Monto_Capital_Pagar' => null,
                'Saldo_Capital' => $capital,
                'Fecha_Inicio' => $i == 1 ? $fechaInicio : null,
                'fecha_vencimiento' => null,
                'MOnto_Capital_Mas_Interes_a_Pagar' => 0.00,
                'estado' => 'Pendiente',
            ]);
        }
    }
    public function actualizarPrestamo(Prestamos $prestamo, array $data) {
        $numeroCuotasAnterior = $prestamo->numero_cuotas;
    
        $prestamo->fill([
            'fecha_inicio' => $data['fecha_inicio'] ?? $prestamo->fecha_inicio,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? $prestamo->fecha_vencimiento,
            'capital' => $data['capital'] ?? $prestamo->capital,
            'numero_cuotas' => $data['numero_cuotas'] ?? $prestamo->numero_cuotas,
            'estado_cliente' => $data['estado_cliente'] ?? $prestamo->estado_cliente,
            'recomendado_id' => $data['recomendado_id'] ?? $prestamo->recomendado_id,
            'tasa_interes_diario' => $data['tasa_interes_diario'] ?? $prestamo->tasa_interes_diario,
            'monto_total' => $data['capital'] ?? $prestamo->capital,
        ]);
    
        $prestamo->save();    
        $this->actualizarCuotas($prestamo, $numeroCuotasAnterior);
        return $prestamo;
    }
    
    public function actualizarCuotas(Prestamos $prestamo, int $numeroCuotasAnterior) {
        $cuotasExistentes = Cuotas::where('prestamo_id', $prestamo->id)->orderBy('numero_cuota')->get();
        if ($prestamo->numero_cuotas > $numeroCuotasAnterior) {
            $capital = $prestamo->capital;
            $fechaInicio = Carbon::parse($prestamo->fecha_inicio);
            for ($i = $numeroCuotasAnterior + 1; $i <= $prestamo->numero_cuotas; $i++) {
                Cuotas::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'capital' => $capital,
                    'interes' => 0.00,
                    'Dias' => 0,
                    'Tasa_Interes_Diario' => $prestamo->tasa_interes_diario,
                    'Monto_Interes_Pagar' => 0.00,
                    'Monto_Capital_Pagar' => null,
                    'Saldo_Capital' => $capital,
                    'Fecha_Inicio' => null,
                    'fecha_vencimiento' => null,
                    'MOnto_Capital_Mas_Interes_a_Pagar' => 0.00,
                    'estado' => 'Pendiente',
                ]);
            }
        } 
        else if ($prestamo->numero_cuotas < $numeroCuotasAnterior) {
            $cuotasPagadas = $cuotasExistentes->where('estado', '!=', 'Pendiente')
                                             ->where('numero_cuota', '>', $prestamo->numero_cuotas)
                                             ->count();
            
            if ($cuotasPagadas > 0) {
                throw new \Exception('No se pueden eliminar cuotas que ya han sido pagadas');
            }
            
            Cuotas::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', '>', $prestamo->numero_cuotas)
                ->delete();
        }
        
        Cuotas::where('prestamo_id', $prestamo->id)->update([
            'capital' => $prestamo->capital,
            'Tasa_Interes_Diario' => $prestamo->tasa_interes_diario,
            'Saldo_Capital' => $prestamo->capital
        ]);
        
        $primeraCuota = Cuotas::where('prestamo_id', $prestamo->id)
                             ->where('numero_cuota', 1)
                             ->first();
        
        if ($primeraCuota) {
            $primeraCuota->Fecha_Inicio = $prestamo->fecha_inicio;
            $primeraCuota->save();
        }
    }
}