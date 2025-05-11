<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class ClientePrestamoResource extends JsonResource{
    public function toArray($request) {
        $recomendado = $this->recomendacion;

        $cliente = $this->cliente;
        $fotoPath = 'customers/' . ($cliente->foto ?? '');
        $fotoPorDefecto = 'customers/SinFoto.jpg';
        $fotoUrl = $cliente->foto && File::exists(public_path($fotoPath))
            ? asset($fotoPath)
            : asset($fotoPorDefecto);

        return [
            'nombre' => $cliente->nombre . ' ' . $cliente->apellidos,
            'id' => $cliente->id,
            'dni' => $cliente->dni,
            'foto' => $fotoUrl,
            'Fecha_Inicio' => $this->fecha_inicio->format('d-m-Y H:i:s'),
            'Fecha_Vencimiento' => $this->fecha_vencimiento->format('d-m-Y H:i:s'),
            'capital' => $this->capital,
            'tasa_interes_diario' => $this->tasa_interes_diario,
            'recomendado' => $recomendado ? $recomendado->nombre . ' ' . $recomendado->apellidos : null,
            'Dnirecomendado' => $recomendado ? $recomendado->dni : null,
            'estado_cliente' => $this->estado_cliente,
            'idPrestamo' => $this->id,
        ];
    }
}