<?php

namespace App\Http\Requests\Buscador;
use Illuminate\Foundation\Http\FormRequest;
class PrestamoFilterRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        return [
            'per_page' => 'integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'estado_cliente' => 'nullable|string|max:50',
        ];
    }
}
