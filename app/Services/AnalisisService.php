<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Dominio;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Url;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AnalisisService
{
    public function __construct(private UrlService $urls, private VirusTotalService $virusTotal)
    {
    }

    public function registrarUrl(string $normalizada): Url
    {
        $dominio = Dominio::firstOrCreate([
            'nombre' => $this->urls->host($normalizada),
        ]);

        return Url::firstOrCreate(
            ['url_hash' => hash('sha256', $normalizada)],
            ['id_dominio' => $dominio->getKey(), 'url' => $normalizada]
        );
    }

    // Cada reanálisis crea otro registro para conservar el resultado anterior.
    public function analizar(Usuario $usuario, string $entrada, ?Analisis $anterior = null): Analisis
    {
        $urlNormalizada = $this->urls->normalizar($entrada);
        $url = $this->registrarUrl($urlNormalizada);

        $urlFinal = null;
        $urlObjetivo = $urlNormalizada;

        if ($this->urls->esAcortado($urlNormalizada)) {
            $urlDestino = $this->urls->resolverFinal($urlNormalizada);

            if ($urlDestino !== $urlNormalizada) {
                $urlFinal = $this->registrarUrl($urlDestino);
                $urlObjetivo = $urlDestino;
            }
        }

        $resultado = $this->virusTotal->analizar($urlObjetivo, $anterior !== null);
        $riesgo = TipoRiesgo::where('nombre', $this->clasificar($resultado))->firstOrFail();
        $fuente = FuenteVerificacion::where('nombre', 'VirusTotal')->firstOrFail();

        return DB::transaction(function () use ($usuario, $url, $urlFinal, $riesgo, $fuente, $resultado, $anterior) {
            $analisis = Analisis::create([
                'id_usuario' => $usuario->getKey(),
                'id_url' => $url->getKey(),
                'id_url_final' => $urlFinal?->getKey(),
                'id_tipo_riesgo' => $riesgo->getKey(),
                'id_analisis_anterior' => $anterior?->getKey(),
            ]);

            foreach ($this->filas($resultado) as $detalle) {
                $analisis->detalles()->create($detalle + [
                    'id_fuente' => $fuente->getKey(),
                ]);
            }

            return $analisis;
        });
    }

    public function clasificar(array $resultado): string
    {
        if ($resultado['malicious'] >= config('virustotal.umbral_peligroso')) {
            return 'Peligroso';
        }

        return ($resultado['malicious'] > 0 || $resultado['suspicious'] > 0)
            ? 'Sospechoso'
            : 'Seguro';
    }

    private function filas(array $resultado): array
    {
        if (!$resultado['hallazgos']) {
            return [[
                'motor' => null,
                'resultado' => 'limpio',
                'categoria' => null,
                'descripcion' => 'Ningún motor marcó la URL como riesgosa.',
            ]];
        }

        return $resultado['hallazgos'];
    }
}