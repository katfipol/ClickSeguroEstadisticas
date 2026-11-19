<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Lista de excepciones que no deben reportarse en los logs de error del servidor
     * porque son errores esperados del flujo de usuario.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AnalisisException::class,
        LimiteApiException::class,
    ];

    /**
     * Campos sensibles que jamás deben devolverse en caso de error de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registra el manejo centralizado de excepciones para la API REST.
     */
    public function register(): void
    {
        // 1. Excepciones propias del negocio (AnalisisException y LimiteApiException)
        $this->renderable(function (AnalisisException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $e->getStatusCode());
            }
        });

        // 2. Errores de validación de FormRequests (UrlRequest, RegisterRequest, etc.)
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Los datos enviados no son válidos.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            }
        });

        // 3. Recurso no encontrado en Base de Datos (findOrFail) o Ruta inexistente
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $esModelo = $e->getPrevious() instanceof ModelNotFoundException;

                return response()->json([
                    'status'  => 'error',
                    'message' => $esModelo
                        ? 'El recurso solicitado no existe o no tienes permiso para verlo.'
                        : 'La ruta solicitada no existe en la API.',
                ], Response::HTTP_NOT_FOUND); // 404
            }
        });

        // 4. Usuario no autenticado o Token Bearer inválido/expirado
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No autenticado. Inicia sesión o envía un token válido.',
                ], Response::HTTP_UNAUTHORIZED); // 401
            }
        });

        // 5. Límite de peticiones por minuto superado (Middleware throttle)
        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Has realizado demasiadas peticiones. Por favor, espera un momento antes de intentar de nuevo.',
                ], Response::HTTP_TOO_MANY_REQUESTS); // 429
            }
        });
    }
}