<?php

namespace App\Http\Controllers;

use App\Exceptions\AnalisisException;
use App\Http\Requests\DocumentoRequest;
use App\Http\Resources\AnalisisResource;
use App\Models\Documento;
use App\Services\AnalisisService;
use App\Services\UrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\Response;

/** HU-13: Extracción y análisis de enlaces en documentos PDF o TXT */
class DocumentoController extends Controller
{
    const MAX_ANALIZAR = 20;

    public function __construct(
        private UrlService $urls,
        private AnalisisService $servicio
    ) {
    }

    /**
     * Lista los documentos subidos por el usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $documentos = $request->user()->documentos()
            ->latest('id_documento')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $documentos,
        ], Response::HTTP_OK);
    }

    /**
     * Sube un documento, extrae sus enlaces y pone los análisis en cola (HU-13).
     */
    public function store(DocumentoRequest $request): JsonResponse
    {
        $archivo = $request->file('documento');
        try {
            $texto = $this->leerTexto($archivo->getRealPath(), strtolower($archivo->getClientOriginalExtension()));
        } catch (\Throwable $e) {
            throw new AnalisisException('No se pudo leer el documento. Puede estar cifrado, escaneado o dañado.');
        }

        $enlaces = $this->extraerEnlaces($texto);
        if (!$enlaces) {
            throw new AnalisisException('No se encontraron enlaces válidos en el documento.');
        }

        $aAnalizar = array_slice($enlaces, 0, self::MAX_ANALIZAR);

        $documento = Documento::create([
            'id_usuario'       => $request->user()->id_usuario,
            'nombre'           => mb_substr($archivo->getClientOriginalName(), 0, 255),
            'total_enlaces'    => count($enlaces),
            'enlaces_omitidos' => count($enlaces) - count($aAnalizar),
        ]);

        foreach ($aAnalizar as $url) {
            try {
                $this->servicio->crearPendiente($request->user(), $url, null, $documento->id_documento);
            } catch (AnalisisException $e) {
                // Omitir enlaces individuales inválidos sin detener el proceso
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Documento procesado y análisis puestos en cola.',
            'data'    => $documento,
        ], Response::HTTP_CREATED); // 201
    }

    /**
     * Muestra el documento y el resumen de sus análisis asociados.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $documento = $request->user()->documentos()->findOrFail($id);
       $analisis = $documento->analisis()->with(['url', 'urlFinal', 'tipoRiesgo'])->orderBy('id_analisis')->get();
        $resumen = [
            'seguro'     => $analisis->where('tipoRiesgo.nombre', 'Seguro')->count(),
            'sospechoso' => $analisis->where('tipoRiesgo.nombre', 'Sospechoso')->count(),
            'peligroso'  => $analisis->where('tipoRiesgo.nombre', 'Peligroso')->count(),
            'fallido'    => $analisis->where('estado', 'fallido')->count(),
        ];
        $enCurso = $analisis->whereIn('estado', ['pendiente', 'procesando'])->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'documento' => $documento,
                'analisis'  => AnalisisResource::collection($analisis),
                'resumen'   => $resumen,
                'en_curso'  => $enCurso,
                'terminado' => $enCurso === 0,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Consulta de estado para polling asíncrono.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $documento = $request->user()->documentos()->findOrFail($id);
        $enCurso = $documento->analisis()->whereIn('estado', ['pendiente', 'procesando'])->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id_documento' => $documento->id_documento,
                'terminado'    => $enCurso === 0,
                'en_curso'     => $enCurso,
            ],
        ], Response::HTTP_OK);
    }

    private function leerTexto(string $ruta, string $extension): string
    {
        if ($extension === 'pdf') {
            $texto = (new Parser())->parseFile($ruta)->getText();
            if (preg_match_all('~/URI\s*\(([^)]+)\)~', (string) file_get_contents($ruta), $m)) {
                $texto .= "\n" . implode("\n", $m[1]);
            }
        } else {
            $texto = (string) file_get_contents($ruta);
        }
        return mb_substr($texto, 0, 300000);
    }

    private function extraerEnlaces(string $texto): array
    {
        $acortadores = implode('|', array_map(fn (string $d) => preg_quote($d, '~'), UrlService::ACORTADORES));
        $patron = '~(?:(?:https?://|www\.)[^\s<>"\'\\\\\)\]\}]+|\b(?:(?:' . $acortadores . ')|(?:[a-z0-9-]+\.)+[a-z]{2,})/[^\s<>"\'\\\\\)\]\}]*)~iu';

        preg_match_all($patron, $texto, $m);
        $salida = [];
        foreach ($m[0] as $crudo) {
            try {
                $salida[$this->urls->normalizar(rtrim($crudo, '.,;:!?'))] = true;
            } catch (AnalisisException $e) {
                continue;
            }
        }
        return array_keys($salida);
    }
}