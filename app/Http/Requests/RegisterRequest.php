<?php

namespace App\Http\Requests;

use App\Rules\ContrasenaNoFiltrada;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'           => 'required|string|min:2|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email'            => 'required|email|max:150|unique:usuario,email',
            'password'         => ['required', 'string', 'min:8', 'confirmed', new ContrasenaNoFiltrada()],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre es obligatorio.',
            'nombre.min'         => 'El nombre debe tener al menos 2 caracteres.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.email'        => 'Ingresa un correo electrónico válido.',
            'email.unique'       => 'Ya existe una cuenta con ese correo.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
