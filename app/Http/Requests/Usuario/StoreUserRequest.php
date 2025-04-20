<?php

namespace App\Http\Requests\Usuario;
use Illuminate\Foundation\Http\FormRequest;
class StoreUserRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:120|unique:users',
            'username' => 'required|string|max:30|unique:users',
            'password' => 'required|string|min:8',
            'status' => 'required|string|in:activo,inactivo',
        ];
    }
    public function messages() {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio',
            'username.required' => 'El usuario es obligatorio',
            'password.required' => 'La contrasena es obligatoria',
            'status.required' => 'El estado es obligatorio'
        ];
    }
}
