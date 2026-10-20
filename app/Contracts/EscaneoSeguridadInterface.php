<?php

namespace App\Contracts;

interface EscaneoSeguridadInterface
{
    public function analizar(string $url, bool $forzarNuevo = false): array;

    public function consultarArchivo(string $sha256): ?array;

    public function consultarDominio(string $dominio): ?array;

    public function consultarIp(string $ip): ?array;
}