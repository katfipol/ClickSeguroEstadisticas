<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function getNombreCompletoAttribute(): string
    {
        $partes = [$this->nombre, $this->apellido_paterno, $this->apellido_materno];

        return implode(' ', array_filter(
            $partes,
            fn ($parte) => $parte !== null && $parte !== ''
        ));
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // No se utiliza remember_token en autenticación stateless por API
    }

    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class, 'id_usuario', 'id_usuario');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'id_usuario', 'id_usuario');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'id_usuario', 'id_usuario');
    }

    public function archivosVerificados(): HasMany
    {
        return $this->hasMany(ArchivoVerificado::class, 'id_usuario', 'id_usuario');
    }
}