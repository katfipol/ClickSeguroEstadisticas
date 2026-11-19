<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/** HU-17: Informes descargables en PDF para la API. */
class InformeController extends Controller
{
    public function analisis(Request $request, int $id)
    {
        $analisis = $request->user()->analisis()
            ->with(['url.dominio', 'urlFinal.dominio', 'tipoRiesgo', 'detalles.fuenteVerificacion', 'anterior.tipoRiesgo', 'anterior.detalles'])
            ->findOrFail($id);
        
        abort_unless($analisis->estado === 'completado', 404);

        $codigo = $this->codigo('A', $analisis->id_analisis, $analisis->id_usuario, $analisis->created_at);
        
        return Pdf::loadView('informes.analisis', compact('analisis', 'codigo'))
            ->setPaper('letter')->download("informe-{$codigo}.pdf");
    }

    public function archivo(Request $request, int $id)
    {
        $archivo = $request->user()->archivosVerificados()->with(['tipoRiesgo', 'detalles.fuenteVerificacion'])->findOrFail($id);
        
        $codigo = $this->codigo('F', $archivo->id_archivo, $archivo->id_usuario, $archivo->created_at);
        
        return Pdf::loadView('informes.archivo', compact('archivo', 'codigo'))
            ->setPaper('letter')->download("informe-{$codigo}.pdf");
    }

    private function codigo(string $tipo, int $id, int $usuarioId, $fecha): string
    {
        return 'CS-' . strtoupper(substr(hash_hmac('sha256', "$tipo|$id|$usuarioId|$fecha", (string) config('app.key')), 0, 12));
    }
}