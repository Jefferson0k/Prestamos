<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ClienteRequest extends FormRequest {
    public function authorize() {
        return true;
    }
    public function rules() {
        $id = $this->route('cliente')?->id;
        return [
            'dni' => "required|max:20|unique:clientes,dni,$id",
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'correo' => "nullable|email|max:150|unique:clientes,correo,$id",
            'centro_trabajo' => 'nullable|string|max:150',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
