<?php

namespace App\Http\Resources\Prestamo;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;
class PrestamoCollection extends JsonResource{
    protected $prestamo;
    public function __construct($resource, $prestamo = null){
        parent::__construct($resource);
        $this->prestamo = $prestamo;
    }
    public function toArray($request){
        $prestamo = $this->prestamo;
        $estadoCliente = $prestamo ? ($prestamo->estado_cliente == 1 ? 'Pendiente' : 'Pagado') : null;
        $recomendado = $prestamo ? $prestamo->recomendacion : null;
        $usuario = $prestamo ? $prestamo->user : null;

        $fotoPath = 'customers/' . ($this->foto ?? '');
        $fotoPorDefecto = 'customers/SinFoto.jpg';
        $fotoUrl = $this->foto && File::exists(public_path($fotoPath)) 
            ? asset($fotoPath) 
            : asset($fotoPorDefecto);

        return [
            'nombre' => $this->nombre . ' ' . $this->apellidos,
            'id' => $this->id,
            'dni' => $this->dni,
            'foto' => $fotoUrl,
            'Fecha_Inicio' => $prestamo ? $prestamo->fecha_inicio->format('d-m-Y H:i:s') : '00-00-00 00:00:00',
            'Fecha_Vencimiento' => $prestamo ? $prestamo->fecha_vencimiento->format('d-m-Y H:i:s') : '00-00-00 00:00:00',
            'capital' => $prestamo ? $prestamo->capital : 0.00,
            'tasa_interes_diario' => $prestamo ? $prestamo->tasa_interes_diario : 0.00,
            'recomendado' => $recomendado ? $recomendado->nombre . ' ' . $recomendado->apellidos : null,
            'Dnirecomendado' => $recomendado?->dni,
            'estado_cliente' => $estadoCliente,
            'idPrestamo' => $prestamo?->id,
            'referencia' => $prestamo?->referencia,
            'usuario_id' => $prestamo?->usuario_id,
            'usuario' => $usuario ? [
                'name' => $usuario->name. ' ' .$usuario->apellidos,
                'dni' => $usuario->dni,
                'username' => $usuario->username,
            ] : null,
        ];
    }
}
