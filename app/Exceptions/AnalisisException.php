<?php

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Excepción de dominio con mensaje seguro para mostrar al cliente de la API.
 */
class AnalisisException extends RuntimeException
{
    /**
     * Código de estado HTTP asociado al error de negocio.
     */
    protected int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}