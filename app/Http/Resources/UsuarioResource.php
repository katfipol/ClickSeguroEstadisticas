<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transforma el modelo Usuario en una estructura JSON estandarizada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id_usuario'       => $this->id_usuario,
            'nombre'           => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombre_completo'  => $this->nombre_completo,
            'email'            => $this->email,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}