<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $fillable = ['nombre', 'apellido_paterno', 'apellido_materno', 'email', 'password'];
    protected $hidden = ['password'];

    public function getNombreCompletoAttribute(): string
    {
        $partes = [$this->nombre, $this->apellido_paterno, $this->apellido_materno];
        return implode(' ', array_filter($partes, fn ($parte) => $parte !== null && $parte !== ''));
    }

    // La tabla no tiene remember_token (no se requiere "recordarme").
    public function getRememberTokenName()
    {
        return '';
    }

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'usuario_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'usuario_id');
    }
}