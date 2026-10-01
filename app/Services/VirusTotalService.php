<?php

namespace App\Services;

use App\Exceptions\AnalisisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Único punto de contacto con VirusTotal. Devuelve una estructura interna:
 * ['malicious' => int, 'suspicious' => int,
 *  'hallazgos' => [['motor','resultado','categoria','descripcion'], ...]]
 */
class VirusTotalService
{
    public function analizar(string $url): array
    {
        $key = config('virustotal.api_key');
        if (!$key) {
            throw new AnalisisException('El servicio de análisis no está configurado.');
        }
        $base = config('virustotal.base_url');
        $http = Http::withHeaders(['x-apikey' => $key, 'accept' => 'application/json'])
            ->timeout(config('virustotal.timeout'));

        try {
            $envio = $http->asForm()->post("$base/urls", ['url' => $url]);
            $this->verificar($envio->status());
            $id = $envio->json('data.id');
            if (!$id) {
                throw new AnalisisException('Respuesta inesperada del servicio de análisis.');
            }
            for ($i = 0; $i < config('virustotal.max_polls'); $i++) {
                $r = $http->get("$base/analyses/$id");
                $this->verificar($r->status());
                if ($r->json('data.attributes.status') === 'completed') {
                    return $this->transformar($r->json('data.attributes') ?? []);
                }
                sleep(3);
            }
        } catch (ConnectionException $e) {
            throw new AnalisisException('No se pudo contactar al servicio de análisis. Intenta de nuevo.');
        }
        throw new AnalisisException('El análisis aún no termina. Intenta de nuevo en unos segundos.');
    }

    private function verificar(int $status): void
    {
        if ($status === 429) {
            throw new AnalisisException('Se alcanzó el límite de consultas. Espera un minuto e intenta de nuevo.');
        }
        if ($status >= 400) {
            throw new AnalisisException('El servicio de análisis respondió con un error (' . $status . ').');
        }
    }

    /** Conserva solo lo necesario: conteos y motores que marcaron la URL. */
    private function transformar(array $attr): array
    {
        $stats = $attr['stats'] ?? [];
if ($stats === []) {
    throw new AnalisisException(
        'El servicio de análisis no devolvió datos suficientes. Intenta de nuevo.'
    );
}
        $hallazgos = [];
        foreach (($attr['results'] ?? []) as $nombre => $r) {
            $cat = $r['category'] ?? '';
            if (!in_array($cat, ['malicious', 'suspicious'], true)) {
                continue;
            }
            $motor = $r['engine_name'] ?? $nombre;
            $etiqueta = $r['result'] ?? null;
            $hallazgos[] = [
                'motor'       => mb_substr($motor, 0, 100),
                'resultado'   => $cat === 'malicious' ? 'malicioso' : 'sospechoso',
                'categoria'   => $etiqueta ? mb_substr($etiqueta, 0, 100) : null,
                'descripcion' => mb_substr("$motor la marcó como " . ($etiqueta ?: $cat), 0, 255),
            ];
        }
        return [
            'malicious'  => (int) ($stats['malicious'] ?? 0),
            'suspicious' => (int) ($stats['suspicious'] ?? 0),
            'hallazgos'  => $hallazgos,
        ];
    }
}
