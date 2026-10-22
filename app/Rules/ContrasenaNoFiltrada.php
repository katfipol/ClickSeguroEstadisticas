<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

/**
 * HU-15: rechaza contraseñas que aparecen en filtraciones públicas (Pwned Passwords).
 * Usa k-anonimato: solo se envían los primeros 5 caracteres de la huella SHA-1.
 * Si el servicio no responde, la regla no bloquea el registro.
 */
class ContrasenaNoFiltrada implements Rule
{
    public function passes($attribute, $value): bool
    {
        try {
            $sha = strtoupper(sha1((string) $value));
            $r = Http::timeout(4)->withHeaders(['Add-Padding' => 'true'])
                ->get('https://api.pwnedpasswords.com/range/' . substr($sha, 0, 5));
            if (!$r->ok()) {
                return true;
            }
            foreach (explode("\n", $r->body()) as $linea) {
                [$sufijo, $veces] = array_pad(explode(':', trim($linea)), 2, '0');
                if ($sufijo === substr($sha, 5)) {
                    return (int) $veces === 0; // con Add-Padding hay filas de relleno con 0
                }
            }
            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function message(): string
    {
        return 'Esa contraseña aparece en filtraciones públicas. Elige otra.';
    }
}
