<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['documento' => 'required|file|mimes:pdf,txt|max:5120']; // 5 MB; valida el tipo real del contenido
    }

    public function messages(): array
    {
        return [
            'documento.required' => 'Selecciona un documento.',
            'documento.mimes'    => 'Solo se aceptan archivos PDF o TXT.',
            'documento.max'      => 'El documento supera el límite de 5 MB.',
            'documento.uploaded' => 'No se pudo subir el documento (¿supera el límite de 5 MB?).',
        ];
    }
}
