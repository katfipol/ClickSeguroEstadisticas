<?php

namespace App\Http\Controllers;

use App\Exceptions\AnalisisException;
use App\Http\Requests\UrlRequest;
use App\Services\AnalisisService;
use Illuminate\Http\Request;

class AnalisisController extends Controller
{
    public function __construct(private AnalisisService $servicio)
    {
    }

    public function dashboard(Request $request)
    {
        $recientes = $request->user()->analisis()->with(['url', 'tipoRiesgo'])->latest('id')->limit(5)->get();
        return view('analisis.dashboard', compact('recientes'));
    }

    public function store(UrlRequest $request)
    {
        try {
            $analisis = $this->servicio->analizar($request->user(), $request->url);
        } catch (AnalisisException $e) {
            return back()->withInput()->withErrors(['url' => $e->getMessage()]);
        }
        return redirect()->route('analisis.show', $analisis->id);
    }

    public function show(Request $request, int $id)
    {
        // Siempre acotado al usuario autenticado: otro usuario recibe 404.
        $analisis = $request->user()->analisis()
            ->with(['url.dominio', 'urlFinal.dominio', 'tipoRiesgo', 'detalles.fuenteVerificacion', 'anterior.tipoRiesgo', 'anterior.detalles'])
            ->findOrFail($id);
        return view('analisis.show', compact('analisis'));
    }

    public function history(Request $request)
    {
        $analisis = $request->user()->analisis()->with(['url', 'urlFinal', 'tipoRiesgo'])->latest('id')->simplePaginate(10);
        return view('analisis.history', compact('analisis'));
    }

    public function reanalizar(Request $request, int $id)
    {
        $anterior = $request->user()->analisis()->with('url')->findOrFail($id);
        try {
            $nuevo = $this->servicio->analizar($request->user(), $anterior->url->url, $anterior);
        } catch (AnalisisException $e) {
            return redirect()->route('analisis.show', $id)->withErrors(['url' => $e->getMessage()]);
        }
        return redirect()->route('analisis.show', $nuevo->id);
    }

    public function destroy(Request $request, int $id)
    {
        $request->user()->analisis()->findOrFail($id)->delete();
        return redirect()->route('historial')->with('ok', 'Análisis eliminado.');
    }
}
