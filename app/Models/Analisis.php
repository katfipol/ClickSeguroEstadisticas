<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analisis extends Model
{
    const UPDATED_AT = null;

    protected $table = 'analisis';

    protected $primaryKey = 'id_analisis';

    protected $fillable = [
        'id_usuario',
        'id_url',
        'id_url_final',
        'id_tipo_riesgo',
        'id_analisis_anterior',
        'id_documento',
        'estado',
        'error',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'id_url', 'id_url');
    }

    public function urlFinal(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'id_url_final', 'id_url');
    }

    public function tipoRiesgo(): BelongsTo
    {
        return $this->belongsTo(TipoRiesgo::class, 'id_tipo_riesgo', 'id_tipo_riesgo');
    }

    public function anterior(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'id_analisis_anterior', 'id_analisis');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id_documento');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAnalisis::class, 'id_analisis', 'id_analisis');
    }

    public function estaTerminado(): bool
    {
        return in_array($this->estado, ['completado', 'fallido'], true);
    }

    public function urlEvaluada(): Url
    {
        return $this->urlFinal ?? $this->url;
    }

    public function getDeteccionesAttribute(): int
    {
        return $this->detalles->filter(function ($d) {
            if (empty($d->motor) || strtolower($d->motor) === 'resumen general') {
                return false;
            }
            $res = strtolower(trim($d->resultado ?? ''));
            return !in_array($res, ['limpio', 'clean', 'harmless', 'seguro', 'undetected', 'unrated', ''], true);
        })->count();
    }

    // Traduce automaticamente los resultados y categorias de VirusTotal al espanol
    public static function traducirTermino(?string $texto): string
    {
        if ($texto === null || trim($texto) === '') {
            return '—';
        }

        $limpio = strtolower(trim($texto));
        $diccionario = [
            'malicious'            => 'Malicioso',
            'suspicious'           => 'Sospechoso',
            'phishing'             => 'Phishing / Suplantación',
            'malware'              => 'Malware / Código dañino',
            'harmless'             => 'Inofensivo / Seguro',
            'clean'                => 'Limpio',
            'undetected'           => 'Sin detecciones',
            'unrated'              => 'Sin calificar',
            'spam'                 => 'Spam / Correo no deseado',
            'scam'                 => 'Estafa / Fraude',
            'ransomware'           => 'Ransomware / Secuestro de datos',
            'trojan'               => 'Troyano',
            'spyware'              => 'Spyware / Espía',
            'adware'               => 'Adware / Publicidad invasiva',
            'cryptomining'         => 'Minería oculta de criptomonedas',
            'command and control'  => 'Servidor de Comando y Control (C2)',
            'botnet'               => 'Red Zombi (Botnet)',
        ];

        return $diccionario[$limpio] ?? ucfirst($texto);
    }
}