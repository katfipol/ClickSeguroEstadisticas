<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email'    => 'required|email|max:150|unique:usuario,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'apellido_paterno.string' => 'El apellido paterno debe ser texto.',
            'apellido_paterno.max' => 'El apellido paterno debe tener como máximo 100 caracteres.',
            'apellido_materno.string' => 'El apellido materno debe ser texto.',
            'apellido_materno.max' => 'El apellido materno debe tener como máximo 100 caracteres.',
            'email.unique'       => 'Ya existe una cuenta con ese correo.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}