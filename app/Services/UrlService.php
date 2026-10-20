<?php

namespace App\Services;

use App\Exceptions\AnalisisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UrlService
{
    const MAX_REDIRECCIONES = 5;

    const ACORTADORES = [
        'bit.ly', 't.co', 'tinyurl.com', 'goo.gl', 'ow.ly', 'is.gd', 'buff.ly', 'rebrand.ly',
        'cutt.ly', 'shorturl.at', 'tiny.cc', 't.ly', 'rb.gy', 'lnkd.in', 's.id', 'bit.do', 'v.gd',
    ];

    /** Valida y normaliza: solo http/https, sin credenciales, host en minúsculas, sin fragmento ni puerto por defecto. */
    public function normalizar(string $entrada): string
    {
        $u = trim($entrada);
        if ($u === '' || strlen($u) > 2048 || preg_match('/\s/', $u)) {
            throw new AnalisisException('La URL no es válida.');
        }
        if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $u)) {
            $u = 'http://' . $u;
        }
        $p = parse_url($u);
        $scheme = strtolower($p['scheme'] ?? '');
        if (!$p || empty($p['host']) || !in_array($scheme, ['http', 'https'], true)) {
            throw new AnalisisException('Solo se aceptan URLs http o https.');
        }
        if (isset($p['user']) || isset($p['pass'])) {
            throw new AnalisisException('La URL no puede incluir usuario o contraseña.');
        }
        $host = strtolower($p['host']);
        if (!filter_var($host, FILTER_VALIDATE_IP) && !preg_match('/^([a-z0-9-]+\.)+[a-z0-9-]{2,}$/', $host)) {
            throw new AnalisisException('El dominio de la URL no es válido.');
        }
        $puerto = $p['port'] ?? null;
        if ($puerto === ($scheme === 'https' ? 443 : 80)) {
            $puerto = null;
        }
        return $scheme . '://' . $host . ($puerto ? ':' . $puerto : '')
            . ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    public function host(string $urlNormalizada): string
    {
        return strtolower(parse_url($urlNormalizada, PHP_URL_HOST));
    }

    public function esAcortado(string $urlNormalizada): bool
    {
        return in_array($this->host($urlNormalizada), self::ACORTADORES, true);
    }

    public function ipPublica(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Sigue redirecciones desde el servidor (nunca desde el navegador). Anti-SSRF:
     * solo puertos 80/443, todas las IP del host deben ser públicas y la conexión
     * se fija a la IP validada (CURLOPT_RESOLVE) para evitar DNS rebinding.
     */
    public function resolverFinal(string $url): string
    {
        $actual = $url;
        for ($i = 0; $i <= self::MAX_REDIRECCIONES; $i++) {
            $p = parse_url($actual);
            $puerto = $p['port'] ?? ($p['scheme'] === 'https' ? 443 : 80);
            if (!in_array($puerto, [80, 443], true)) {
                throw new AnalisisException('La redirección usa un puerto no permitido.');
            }
            $ip = $this->ipSegura($p['host']);
            try {
                $resp = Http::withHeaders(['User-Agent' => 'ClickSeguro/1.0'])
                    ->withOptions([
                        'timeout' => 5, 'connect_timeout' => 3, 'allow_redirects' => false,
                        'curl' => [CURLOPT_RESOLVE => ["{$p['host']}:{$puerto}:{$ip}"]],
                    ])->head($actual);
            } catch (ConnectionException $e) {
                throw new AnalisisException('No se pudo resolver el enlace acortado.');
            }
            $loc = $resp->header('Location');
            if (!in_array($resp->status(), [301, 302, 303, 307, 308], true) || $loc === '') {
                return $actual;
            }
            $actual = $this->normalizar($this->absoluta($loc, $actual));
        }
        throw new AnalisisException('El enlace tiene demasiadas redirecciones.');
    }

    private function ipSegura(string $host): string
    {
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (!$ips) {
            throw new AnalisisException('No se pudo resolver el dominio del enlace.');
        }
        foreach ($ips as $ip) {
            if (!$this->ipPublica($ip)) {
                throw new AnalisisException('El enlace apunta a una dirección no permitida.');
            }
        }
        return $ips[0];
    }

    private function absoluta(string $loc, string $base): string
    {
        if (preg_match('#^https?://#i', $loc)) {
            return $loc;
        }
        $p = parse_url($base);
        $origen = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        if (str_starts_with($loc, '//')) {
            return $p['scheme'] . ':' . $loc;
        }
        if (str_starts_with($loc, '/')) {
            return $origen . $loc;
        }
        return $origen . rtrim(dirname($p['path'] ?? '/'), '/') . '/' . $loc;
    }
}
