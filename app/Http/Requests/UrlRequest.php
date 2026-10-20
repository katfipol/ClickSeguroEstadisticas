<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UrlRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['url' => 'required|string|max:2048'];
    }

    public function messages(): array
    {
        return ['url.required' => 'Ingresa una URL.', 'url.max' => 'La URL es demasiado larga.'];
    }
}
