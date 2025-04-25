<?php

namespace App\Http\Requests\Usuario;
use Illuminate\Foundation\Http\FormRequest;
class StoreUserRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        return [
            'dni' => 'required|digits:8|unique:users,dni',
            'name' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'nacimiento' => 'required|date|before:today',
            'email' => 'required|string|email|max:120|unique:users,email',
            'username' => 'required|string|max:30|unique:users,username',
            'password' => 'required|string|min:8',
            'status' => 'required',
        ];
    }
    public function messages(){
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.integer' => 'El DNI debe ser un número entero.',
            'dni.unique' => 'Este DNI ya está registrado.',

            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',

            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos deben ser texto.',
            'apellidos.max' => 'Los apellidos no deben exceder los 100 caracteres.',

            'nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'nacimiento.date' => 'La fecha de nacimiento debe ser válida.',
            'nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.max' => 'El correo no debe exceder los 120 caracteres.',
            'email.unique' => 'Este correo ya está registrado.',

            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.max' => 'El usuario no debe exceder los 30 caracteres.',
            'username.unique' => 'Este usuario ya está registrado.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',

            'status.required' => 'El estado es obligatorio.',
        ];
    }
}
