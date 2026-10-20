<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $datos = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($datos)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }
        return back()->withErrors(['email' => 'Correo o contraseña incorrectos.'])->onlyInput('email');
    }

    public function showRegister() { return view('auth.register'); }

    public function register(RegisterRequest $request)
    {
        $datos = $request->validated();
        $usuario = Usuario::create([
            'nombre'   => $datos['nombre'],
            'apellido_paterno' => $datos['apellido_paterno'] ?? null,
            'apellido_materno' => $datos['apellido_materno'] ?? null,
            'email'    => $datos['email'],
            'password' => Hash::make($datos['password']),
        ]);
        Auth::login($usuario);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}