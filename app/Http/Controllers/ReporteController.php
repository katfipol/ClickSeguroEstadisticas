<?php

namespace App\Http\Controllers;

use App\Exceptions\AnalisisException;
use App\Http\Requests\ReporteRequest;
use App\Services\AnalisisService;
use App\Services\UrlService;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function create(Request $request)
    {
        $reportes = $request->user()->reportes()->with('url')->latest('id_reporte')->limit(10)->get();
        return view('reportes.create', compact('reportes'));
    }

    public function store(ReporteRequest $request, UrlService $urls, AnalisisService $servicio)
    {
        try {
            $url = $servicio->registrarUrl($urls->normalizar($request->url));
        } catch (AnalisisException $excepcion) {
            return back()->withInput()->withErrors(['url' => $excepcion->getMessage()]);
        }
        if ($request->user()->reportes()->where('id_url', $url->getKey())->exists()) {
            return back()->withInput()->withErrors(['url' => 'Ya reportaste esta URL.']);
        }
        $request->user()->reportes()->create([
            'id_url' => $url->getKey(),
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ]);
        return redirect()->route('reportes.create')->with('ok', 'Reporte registrado. Gracias por ayudar.');
    }
}