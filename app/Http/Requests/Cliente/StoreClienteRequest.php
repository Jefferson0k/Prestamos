<?php

namespace App\Http\Requests\Cliente;
use Illuminate\Foundation\Http\FormRequest;
class StoreClienteRequest extends FormRequest {
    public function authorize() {
        return true;
    }
    public function rules() {
        return [
            'dni' => 'required|digits:8|unique:clientes,dni',
            'nombre' => 'required|string|min:2|max:100',
            'apellidos' => 'required|string|min:2|max:100',
            'telefono' => 'required|numeric|min_digits:9',
            'direccion' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\-,.#°\/]+$/'],
            'correo' => 'required|unique:clientes,correo',
            'centro_trabajo' => 'required|string|max:150',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    public function messages() {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'El DNI ya está registrado.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',

            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',
            'apellidos.min' => 'Los apellidos deben tener al menos 2 caracteres.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 100 caracteres.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono debe contener solo números.',
            'telefono.min_digits' => 'El teléfono debe tener al menos 9 dígitos.',

            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.string' => 'La dirección debe ser una cadena de texto.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'direccion.regex' => 'La dirección contiene caracteres no permitidos.',

            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico debe ser una dirección válida.',
            'correo.max' => 'El correo electrónico no puede tener más de 150 caracteres.',
            'correo.unique' => 'El correo electrónico ya está registrado.',

            'centro_trabajo.required' => 'El centro de trabajo es obligatorio.',
            'centro_trabajo.string' => 'El centro de trabajo debe ser una cadena de texto.',
            'centro_trabajo.max' => 'El centro de trabajo no puede tener más de 150 caracteres.',

            'foto.required' => 'La foto es obligatoria.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La foto debe estar en formato JPG, JPEG o PNG.',
            'foto.max' => 'La foto no puede ser mayor a 2MB.',
        ];
    }
}
