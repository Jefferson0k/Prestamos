<?php

namespace App\Http\Requests\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        $userId = $this->route('user')->id;
        
        return [
            'dni' => ['required', 'string', 'max:8', Rule::unique('users', 'dni')->ignore($userId)],
            'name' => ['nullable', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:100'],
            'nacimiento' => ['nullable', 'date'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'status' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
    public function messages(): array{
        return [
            'dni.required' => 'El DNI es obligatorio',
            'dni.unique' => 'El DNI ya está registrado',
            'dni.max' => 'El DNI debe tener máximo 8 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Ingrese un correo electrónico válido',
            'email.unique' => 'El correo electrónico ya está registrado',
            'username.unique' => 'El nombre de usuario ya está registrado',
            'status.required' => 'El estado es obligatorio',
            'status.boolean' => 'El estado debe ser verdadero o falso',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}