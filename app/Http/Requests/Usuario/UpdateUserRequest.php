<?php

namespace App\Http\Requests\Usuario;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:120|unique:users,email,' . $this->user->id,
            'username' => 'required|string|max:30|unique:users,username,' . $this->user->id,
            'status' => 'required|string|in:activo,inactivo',
        ];
    }
    public function messages(){
        return[
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'username.required' => 'El usuario es obligatorio',
            'status.requited' => 'El estado es obligatorio'
        ];
    }
}
