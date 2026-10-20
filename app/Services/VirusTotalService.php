<?php

namespace App\Services;

use App\Contracts\EscaneoSeguridadInterface;
use App\Exceptions\AnalisisException;
use App\Exceptions\LimiteApiException;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VirusTotalService implements EscaneoSeguridadInterface
{
    // ---------------- URLs ----------------
    public function analizar(string $url, bool $forzarNuevo = false): array
    {
        $id = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

        // 1) Informe ya existente: instantáneo para URLs conocidas. Un reanálisis pide escaneo nuevo.
        if (!$forzarNuevo && ($informe = $this->informeUrl($id))) {
            return $informe;
        }

        // 2) Escaneo nuevo y consulta de su resultado.
        $envio = $this->peticion('post', '/urls', ['url' => $url]);
        $analisisId = $envio->json('data.id');
        if (!$analisisId) {
            throw new AnalisisException('Respuesta inesperada del servicio de análisis.');
        }
        for ($i = 0; $i < config('virustotal.max_polls'); $i++) {
            sleep(4);
            $r = $this->peticion('get', "/analyses/$analisisId");
            if ($r->json('data.attributes.status') === 'completed') {
                return $this->transformar(
                    $r->json('data.attributes.stats') ?? [],
                    $r->json('data.attributes.results') ?? []
                );
            }
        }

        // 3) El escaneo no terminó a tiempo: usar el último informe disponible, si existe.
        if ($informe = $this->informeUrl($id)) {
            return $informe;
        }
        throw new AnalisisException('VirusTotal tardó demasiado en responder. Intenta otra vez en un minuto.');
    }

    private function informeUrl(string $id): ?array
    {
        $r = $this->peticion('get', "/urls/$id", [], [404]);
        $resultados = $r->status() === 404 ? [] : ($r->json('data.attributes.last_analysis_results') ?? []);
        return $resultados
            ? $this->transformar($r->json('data.attributes.last_analysis_stats') ?? [], $resultados)
            : null;
    }

    // ---------------- Archivos (HU-11) ----------------
    /** Devuelve el análisis de la huella, o null si VirusTotal no conoce el archivo. */
    public function consultarArchivo(string $sha256): ?array
    {
        $r = $this->peticion('get', '/files/' . strtolower($sha256), [], [404]);
        if ($r->status() === 404) {
            return null;
        }
        $resultados = $r->json('data.attributes.last_analysis_results') ?? [];
        return $resultados
            ? $this->transformar($r->json('data.attributes.last_analysis_stats') ?? [], $resultados)
            : null;
    }

    // ---------------- Dominios e IPs (HU-12) ----------------
    public function consultarDominio(string $dominio): ?array
    {
        $r = $this->peticion('get', "/domains/$dominio", [], [404]);
        if ($r->status() === 404) {
            return null;
        }
        $a = $r->json('data.attributes') ?? [];
        $creacion = !empty($a['creation_date']) ? Carbon::createFromTimestamp((int) $a['creation_date']) : null;
        return [
            'creacion'    => $creacion,
            'registrador' => isset($a['registrar']) ? mb_substr($a['registrar'], 0, 150) : null,
            'reputacion'  => isset($a['reputation']) ? (int) $a['reputation'] : null,
        ];
    }

    public function consultarIp(string $ip): ?array
    {
        $r = $this->peticion('get', "/ip_addresses/$ip", [], [404]);
        if ($r->status() === 404) {
            return null;
        }
        $a = $r->json('data.attributes') ?? [];
        return [
            'pais'        => isset($a['country']) ? strtoupper(substr($a['country'], 0, 2)) : null,
            'propietario' => $a['as_owner'] ?? null,
            'reputacion'  => isset($a['reputation']) ? (int) $a['reputation'] : null,
        ];
    }

    // ---------------- Internos ----------------
    /** @param int[] $toleradas códigos HTTP que se devuelven sin lanzar error (p. ej. 404) */
    private function peticion(string $metodo, string $ruta, array $datos = [], array $toleradas = []): Response
    {
        $key = config('virustotal.api_key');
        if (!$key) {
            throw new AnalisisException('El servicio de análisis no está configurado.');
        }
        set_time_limit(120);
        $http = Http::withHeaders(['x-apikey' => $key, 'accept' => 'application/json'])
            ->timeout(config('virustotal.timeout'));
        try {
            $r = $metodo === 'post'
                ? $http->asForm()->post(config('virustotal.base_url') . $ruta, $datos)
                : $http->get(config('virustotal.base_url') . $ruta);
        } catch (ConnectionException $e) {
            throw new AnalisisException('No se pudo contactar al servicio de análisis. Intenta de nuevo.');
        }
        if (in_array($r->status(), $toleradas, true)) {
            return $r;
        }
        if ($r->status() === 429) {
            throw new LimiteApiException('Se alcanzó el límite de consultas. Espera un minuto e intenta de nuevo.');
        }
        if ($r->status() >= 400) {
            throw new AnalisisException('El servicio de análisis respondió con un error (' . $r->status() . ').');
        }
        return $r;
    }

    /** Conserva solo lo necesario: conteos y motores que marcaron el objeto. */
    private function transformar(array $stats, array $resultados): array
    {
        $hallazgos = [];
        foreach ($resultados as $nombre => $r) {
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
