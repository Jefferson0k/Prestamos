<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class PrestamoCollection extends JsonResource{
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
            'id' => $this->id,
            'dni' => $this->dni,
            'foto' => $fotoUrl,
            'Fecha_Inicio' => $primerPrestamo ? $primerPrestamo->fecha_inicio->format('d-m-Y H:i:s') : '00-00-00 00:00:00',
            'Fecha_Vencimiento' => $primerPrestamo ? $primerPrestamo->fecha_vencimiento->format('d-m-Y H:i:s') : '00-00-00 00:00:00',
            'capital' => $primerPrestamo ? $primerPrestamo->capital : 00.00,
            'tasa_interes_diario' => $primerPrestamo ? $primerPrestamo->tasa_interes_diario : 00.00,
            'recomendado' => $recomendado ? $recomendado->nombre . ' ' . $recomendado->apellidos : null,
            'Dnirecomendado' => $recomendado ? $recomendado->dni : null,
            'estado_cliente' => $estadoCliente,
            'idPrestamo' => $primerPrestamo ? $primerPrestamo->id : null,
        ];
    }
}