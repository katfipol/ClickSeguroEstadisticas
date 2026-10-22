<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchivoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sha256' => ['required', 'regex:/^[a-fA-F0-9]{64}$/'],
            'nombre' => 'required|string|max:255',
            'tamano' => 'required|integer|min:0|max:52428800', // 50 MB
        ];
    }

    public function messages(): array
    {
        return [
            'sha256.required' => 'La huella del archivo es obligatoria.',
            'sha256.regex'    => 'La huella del archivo no es válida. Vuelve a seleccionar el archivo.',
            'nombre.required' => 'El nombre del archivo es obligatorio.',
            'tamano.required' => 'El tamaño del archivo es obligatorio.',
            'tamano.integer'  => 'El tamaño debe ser un número entero.',
            'tamano.min'      => 'El tamaño no puede ser negativo.',
            'tamano.max'      => 'El archivo supera el límite de 50 MB.',
        ];
    }
}
