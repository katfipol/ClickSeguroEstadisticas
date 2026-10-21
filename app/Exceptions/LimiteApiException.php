<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * El servicio externo rechazó la consulta por límite de cuota (HTTP 429).
 */
class LimiteApiException extends AnalisisException
{
    /**
     * Código HTTP 429 (Too Many Requests).
     */
    protected int $statusCode = Response::HTTP_TOO_MANY_REQUESTS; // 429
}