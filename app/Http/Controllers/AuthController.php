<?php

namespace App\Http\Controllers; 

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Inicia sesión y emite un token Bearer de Sanctum (HU-01).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Correo o contraseña incorrectos.',
            ], Response::HTTP_UNAUTHORIZED); // 401
        }

        $token = $usuario->createToken('click-seguro-api')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token'   => $token,
                'usuario' => new UsuarioResource($usuario),
            ],
        ], Response::HTTP_OK); // 200
    }

    /**
     * Registra un nuevo usuario validando que la contraseña no esté filtrada (HU-01, HU-15).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $usuario = Usuario::create([
            'nombre'           => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
        ]);

        $token = $usuario->createToken('click-seguro-api')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Cuenta creada exitosamente.',
            'data'    => [
                'token'   => $token,
                'usuario' => new UsuarioResource($usuario),
            ],
        ], Response::HTTP_CREATED); // 201
    }

    /**
     * Devuelve el perfil del usuario autenticado actualmente.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => new UsuarioResource($request->user()),
        ], Response::HTTP_OK);
    }

    /**
     * Revoca el token Bearer que se utilizó en la petición actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada correctamente.',
        ], Response::HTTP_OK);
    }
}