<?php

namespace App\Services;

use App\Contracts\EscaneoSeguridadInterface;
use App\Exceptions\AnalisisException;
use App\Exceptions\LimiteApiException;
use App\Models\Dominio;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

/** HU-12: ficha de un dominio o IP (antigüedad, ubicación y reputación). */
class FichaDominioService
{
    const DIAS_CACHE = 7;

    public function __construct(
        private EscaneoSeguridadInterface $escaneoService,
        private UrlService $urls
    ) {
    }

    /** @return array{tipo:string, dominio?:Dominio, ip?:string, datos?:array} */
    public function obtener(string $entrada): array
    {
        $n = strtolower(trim($entrada));
        $n = explode('/', preg_replace('#^[a-z]+://#', '', $n))[0];
        if (preg_match('/^([a-z0-9-.]+):[0-9]+$/', $n, $m)) {
            $n = $m[1];
        }

        if (strlen($n) === 0 || strlen($n) > 255) {
            throw new AnalisisException('Escribe un dominio válido (por ejemplo ejemplo.com) o una IP.');
        }

        if (filter_var($n, FILTER_VALIDATE_IP)) {
            if (!$this->urls->ipPublica($n)) {
                throw new AnalisisException('Solo se pueden consultar direcciones IP públicas.');
            }
            return ['tipo' => 'ip', 'ip' => $n, 'datos' => $this->escaneoService->consultarIp($n) ?? []];
        }

        if (!preg_match('/^([a-z0-9-]+\.)+[a-z0-9-]{2,}$/', $n)) {
            throw new AnalisisException('Escribe un dominio válido (por ejemplo ejemplo.com) o una IP.');
        }

        $dominio = Dominio::firstOrCreate(['nombre' => $n]);
        if (!$dominio->consultado_at || $dominio->consultado_at->lt(now()->subDays(self::DIAS_CACHE))) {
            $this->actualizar($dominio);
        }

        return ['tipo' => 'dominio', 'dominio' => $dominio];
    }

    /**
     * Obtiene los últimos 10 análisis completados del usuario para un dominio específico.
     */
    public function analisisPrevios(Usuario $usuario, int $idDominio): Collection
    {
        return $usuario->analisis()
            ->where('estado', 'completado')
            ->where(fn ($q) => $q->whereHas('url', fn ($u) => $u->where('id_dominio', $idDominio))
                ->orWhereHas('urlFinal', fn ($u) => $u->where('id_dominio', $idDominio)))
            ->with(['url', 'tipoRiesgo'])
            ->latest('id_analisis')
            ->limit(10)
            ->get();
    }

    private function actualizar(Dominio $d): void
    {
        try {
            $vt = $this->escaneoService->consultarDominio($d->nombre) ?? [];
        } catch (LimiteApiException $e) {
            throw $e;
        } catch (AnalisisException $e) {
            $vt = [];
        }

        $creacion = $vt['creacion'] ?? null;
        $registrador = $vt['registrador'] ?? null;
        if (!$creacion) {
            [$creacion, $reg] = $this->rdap($d->nombre);
            $registrador = $registrador ?: $reg;
        }

        $ips = @gethostbynamel($d->nombre) ?: [];
        $ip = collect($ips)->first(fn ($i) => $this->urls->ipPublica($i));
        $pais = null;
        if ($ip) {
            try {
                $pais = ($this->escaneoService->consultarIp($ip)['pais'] ?? null);
            } catch (LimiteApiException $e) {
                throw $e;
            } catch (AnalisisException $e) {
                $pais = null;
            }
        }

        $d->update([
            'fecha_creacion' => $creacion?->toDateString(),
            'registrador'    => $registrador,
            'ip'             => $ip,
            'pais'           => $pais,
            'reputacion'     => $vt['reputacion'] ?? null,
            'consultado_at'  => now(),
        ]);
    }

    /** @return array{0:?Carbon,1:?string} */
    private function rdap(string $dominio): array
    {
        try {
            $r = Http::timeout(6)->acceptJson()->get('https://rdap.org/domain/' . $dominio);
            if (!$r->ok()) {
                return [null, null];
            }
            $j = $r->json();
            $creacion = null;
            foreach ($j['events'] ?? [] as $e) {
                if (($e['eventAction'] ?? '') === 'registration') {
                    $creacion = Carbon::parse($e['eventDate']);
                }
            }
            $registrador = null;
            foreach ($j['entities'] ?? [] as $en) {
                if (in_array('registrar', $en['roles'] ?? [], true)) {
                    foreach ($en['vcardArray'][1] ?? [] as $campo) {
                        if (($campo[0] ?? '') === 'fn') {
                            $registrador = mb_substr((string) $campo[3], 0, 150);
                        }
                    }
                }
            }
            return [$creacion, $registrador];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}