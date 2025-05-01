<?php

namespace App\Http\Requests\Usuario;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        $userId = $this->route('id'); 
        return [
            'dni' => 'required|digits:8|unique:clientes,dni,' . $userId,
            'name' => 'required|string|max:100',
            'apellidos' => 'required|string|min:2|max:100',
            'nacimiento' => 'required|date',
            'email' => 'required|string|email|max:120|unique:users,email,' . $userId,
            'username' => 'required|string|max:30|unique:users,username,' . $userId,
        ];
    }
    public function messages(): array{
        return [
            # DNI
            'dni.required' => 'El DNI es obligatorio',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos',
            'dni.unique' => 'El DNI ya está registrado',
            # Nombre
            'name.required' => 'El nombre es obligatorio',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no debe exceder los 100 caracteres',
            # Apellidos
            'apellidos.required' => 'Los apellidos son obligatorios',
            'apellidos.string' => 'Los apellidos deben ser texto',
            'apellidos.min' => 'Los apellidos deben tener al menos 2 caracteres',
            'apellidos.max' => 'Los apellidos no deben exceder los 100 caracteres',
            # Nacimiento
            'nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'nacimiento.date' => 'La fecha de nacimiento debe tener un formato válido',
            # Email
            'email.required' => 'El email es obligatorio',
            'email.string' => 'El email debe ser texto',
            'email.email' => 'El email no tiene un formato válido',
            'email.max' => 'El email no debe exceder los 120 caracteres',
            'email.unique' => 'El email ya está registrado',
            # Username
            'username.required' => 'El nombre de usuario es obligatorio',
            'username.string' => 'El nombre de usuario debe ser texto',
            'username.max' => 'El nombre de usuario no debe exceder los 30 caracteres',
            'username.unique' => 'El nombre de usuario ya está registrado',
            # Status
            'status.required' => 'El estado es obligatorio',
            'status.string' => 'El estado debe ser texto',
        ];
    }
}
