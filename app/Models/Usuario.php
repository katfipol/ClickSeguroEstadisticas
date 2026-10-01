<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
    ];

    protected $hidden = ['password'];

    public function getNombreCompletoAttribute(): string
    {
        $partes = [$this->nombre, $this->apellido_paterno, $this->apellido_materno];

        return implode(' ', array_filter(
            $partes,
            fn ($parte) => $parte !== null && $parte !== ''
        ));
    }

    // El acceso persistente de "recordarme" no forma parte del alcance actual.
    public function getRememberTokenName()
    {
        return '';
    }

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'id_usuario', 'id_usuario');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_usuario', 'id_usuario');
    }
}