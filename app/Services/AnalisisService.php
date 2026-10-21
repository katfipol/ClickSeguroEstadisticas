<?php

namespace App\Services;

use App\Contracts\EscaneoSeguridadInterface;
use App\Jobs\ProcesarAnalisis;
use App\Models\Analisis;
use App\Models\Dominio;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Url;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AnalisisService
{
    public function __construct(
        private UrlService $urls,
        private EscaneoSeguridadInterface $escaneoService
    ) {
    }

    public function registrarUrl(string $normalizada): Url
    {
        $dominio = Dominio::firstOrCreate(['nombre' => $this->urls->host($normalizada)]);

        return Url::firstOrCreate(
            ['url_hash' => hash('sha256', $normalizada)],
            ['id_dominio' => $dominio->id_dominio, 'url' => $normalizada]
        );
    }

    public function crearPendiente(Usuario $usuario, string $entrada, ?Analisis $anterior = null, ?int $documentoId = null): Analisis
    {
        $url = $this->registrarUrl($this->urls->normalizar($entrada));

        $analisis = Analisis::create([
            'id_usuario'           => $usuario->id_usuario,
            'id_url'               => $url->id_url,
            'id_analisis_anterior' => $anterior?->id_analisis,
            'id_documento'         => $documentoId,
            'estado'               => 'pendiente',
        ]);

       ProcesarAnalisis::dispatchAfterResponse($analisis->id_analisis);

        return $analisis->load('url');
    }

    public function procesar(Analisis $analisis): Analisis
    {
        $normal = $analisis->url->url;
        $final = null;
        $objetivo = $normal;

        if ($this->urls->esAcortado($normal)) {
            $destino = $this->urls->resolverFinal($normal);
            if ($destino !== $normal) {
                $final = $this->registrarUrl($destino);
                $objetivo = $destino;
            }
        }

        $resultado = $this->escaneoService->analizar($objetivo, $analisis->id_analisis_anterior !== null);
        $riesgo = TipoRiesgo::where('nombre', $this->clasificar($resultado))->firstOrFail();
        $fuente = FuenteVerificacion::where('nombre', 'VirusTotal')->firstOrFail();

        DB::transaction(function () use ($analisis, $final, $riesgo, $fuente, $resultado) {
            $analisis->detalles()->delete();
            $analisis->update([
                'id_url_final'   => $final?->id_url,
                'id_tipo_riesgo' => $riesgo->id_tipo_riesgo,
                'estado'         => 'completado',
                'error'          => null,
            ]);

            $detalles = array_map(
                fn (array $fila) => $fila + ['id_fuente' => $fuente->id_fuente],
                $this->filas($resultado)
            );
            $analisis->detalles()->createMany($detalles);
        });

        return $analisis->refresh();
    }

    public function clasificar(array $r): string
    {
        if ($r['malicious'] >= config('virustotal.umbral_peligroso')) {
            return 'Peligroso';
        }

        return ($r['malicious'] > 0 || $r['suspicious'] > 0) ? 'Sospechoso' : 'Seguro';
    }

    public function filas(array $r): array
    {
        if (!$r['hallazgos']) {
            return [[
                'motor'       => null,
                'resultado'   => 'limpio',
                'categoria'   => null,
                'descripcion' => 'Ningún motor marcó la URL como riesgosa.',
            ]];
        }

        return $r['hallazgos'];
    }
}