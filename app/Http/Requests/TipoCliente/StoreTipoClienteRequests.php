<?php

namespace App\Http\Requests\TipoCliente;
use Illuminate\Foundation\Http\FormRequest;
class StoreTipoClienteRequests extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function prepareForValidation(): void{
        $this->merge([
            'nombre' => strtolower($this->input('nombre')),
        ]);
    }
    public function rules(): array{
        return [
            'nombre' => 'required|string|max:100|unique:tipo_cliente,nombre',
            'estado' => 'required',
        ];
    }
    public function messages(): array{
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'nombre.unique' => 'El nombre ya está registrado.',

            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
