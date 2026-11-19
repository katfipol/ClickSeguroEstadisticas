<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReporteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['url' => 'required|string|max:2048', 'motivo' => 'nullable|string|max:500'];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'Ingresa la URL que quieres reportar.',
            'url.max'      => 'La URL es demasiado larga.',
            'motivo.max'   => 'El motivo no puede superar los 500 caracteres.',
        ];
    }
}
