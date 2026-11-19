<?php

namespace App\Services;

use App\Contracts\EscaneoSeguridadInterface;
use App\Models\ArchivoVerificado;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/** HU-11: verifica un archivo solo por su huella SHA-256; el archivo nunca llega al servidor. */
class ArchivoService
{
    public function __construct(
        private EscaneoSeguridadInterface $escaneoService,
        private AnalisisService $analisis
    ) {
    }

    public function verificar(Usuario $usuario, string $sha256, string $nombre, int $tamano): ArchivoVerificado
    {
        $sha256 = strtolower($sha256);
        $resultado = $this->escaneoService->consultarArchivo($sha256); // null = desconocido para el motor
        $riesgo = $resultado
            ? TipoRiesgo::where('nombre', $this->analisis->clasificar($resultado))->firstOrFail()
            : null;
        $fuente = FuenteVerificacion::where('nombre', 'VirusTotal')->firstOrFail();

        $archivo = DB::transaction(function () use ($usuario, $sha256, $nombre, $tamano, $resultado, $riesgo, $fuente) {
            $archivo = ArchivoVerificado::create([
                'id_usuario'     => $usuario->id_usuario,
                'nombre'         => mb_substr($nombre, 0, 255),
                'tamano'         => $tamano,
                'sha256'         => $sha256,
                'id_tipo_riesgo' => $riesgo?->id_tipo_riesgo,
            ]);

            if ($resultado) {
                $detalles = array_map(fn (array $f) => [
                    'id_fuente' => $fuente->id_fuente,
                    'motor'     => $f['motor'],
                    'resultado' => $f['resultado'],
                    'categoria' => $f['categoria'],
                ], $this->analisis->filas($resultado));

                $archivo->detalles()->createMany($detalles);
            }

            return $archivo;
        });

        return $archivo->load(['tipoRiesgo', 'detalles.fuenteVerificacion']);
    }
}