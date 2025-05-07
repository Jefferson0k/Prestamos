<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class ClientePrestamoResource extends JsonResource{
    public function toArray($request){
        $primerPrestamo = $this->prestamos->first();
        $estadoCliente = $primerPrestamo ? ($primerPrestamo->estado_cliente == 1 ? 'Pendiente' : 'Pagado') : null;
        $recomendado = $primerPrestamo ? $primerPrestamo->recomendacion : null;
        $fotoPath = 'customers/' . ($this->foto ?? '');
        $fotoPorDefecto = 'customers/1745896984_68104618995b7.jpg';
        $fotoUrl = $this->foto && File::exists(public_path($fotoPath)) 
            ? asset($fotoPath) 
            : asset($fotoPorDefecto);
        return [
            'nombre' => $this->nombre . ' ' . $this->apellidos,
            'dni' => $this->dni,
            'foto' => $fotoUrl,
            'Fecha_Inicio' => $primerPrestamo ? $primerPrestamo->fecha_inicio->format('d-m-Y H:i:s') : null,
            'Fecha_Vencimiento' => $primerPrestamo ? $primerPrestamo->fecha_vencimiento->format('d-m-Y H:i:s') : null,
            'capital' => $primerPrestamo ? $primerPrestamo->capital : null,
            'tasa_interes_diario' => $primerPrestamo ? $primerPrestamo->tasa_interes_diario : null,
            'recomendado' => $recomendado ? $recomendado->nombre . ' ' . $recomendado->apellidos : null,
            'Dnirecomendado' => $recomendado ? $recomendado->dni : null,
            'estado_cliente' => $estadoCliente,
        ];
    }
}