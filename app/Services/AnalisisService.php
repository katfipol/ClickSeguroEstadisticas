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
        $dominio = Dominio::firstOrCreate(['nombre' => $this->urls->host($normalizada)]);
        return Url::firstOrCreate(
            ['url_hash' => hash('sha256', $normalizada)],
            ['dominio_id' => $dominio->id, 'url' => $normalizada]
        );
    }

    /** Crea SIEMPRE un análisis nuevo; un reanálisis enlaza al anterior sin modificarlo. */
    public function analizar(Usuario $usuario, string $entrada, ?Analisis $anterior = null): Analisis
    {
        $normal = $this->urls->normalizar($entrada);
        $url = $this->registrarUrl($normal);

        $final = null;
        $objetivo = $normal;
        if ($this->urls->esAcortado($normal)) {
            $destino = $this->urls->resolverFinal($normal);
            if ($destino !== $normal) {
                $final = $this->registrarUrl($destino);
                $objetivo = $destino;
            }
        }

       $resultado = $this->virusTotal->analizar($objetivo, $anterior !== null);
        $riesgo = TipoRiesgo::where('nombre', $this->clasificar($resultado))->firstOrFail();
        $fuente = FuenteVerificacion::where('nombre', 'VirusTotal')->firstOrFail();

        return DB::transaction(function () use ($usuario, $url, $final, $riesgo, $fuente, $resultado, $anterior) {
            $analisis = Analisis::create([
                'usuario_id'           => $usuario->id,
                'url_id'               => $url->id,
                'url_final_id'         => $final?->id,
                'tipo_riesgo_id'       => $riesgo->id,
                'analisis_anterior_id' => $anterior?->id,
            ]);
            foreach ($this->filas($resultado) as $fila) {
                $analisis->detalles()->create($fila + ['fuente_verificacion_id' => $fuente->id]);
            }
            return $analisis;
        });
    }

    public function clasificar(array $r): string
    {
        if ($r['malicious'] >= config('virustotal.umbral_peligroso')) {
            return 'Peligroso';
        }
        return ($r['malicious'] > 0 || $r['suspicious'] > 0) ? 'Sospechoso' : 'Seguro';
    }

    private function filas(array $r): array
    {
        if (!$r['hallazgos']) {
            return [[
                'motor' => null, 'resultado' => 'limpio', 'categoria' => null,
                'descripcion' => 'Ningún motor marcó la URL como riesgosa.',
            ]];
        }
        return $r['hallazgos'];
    }
}
