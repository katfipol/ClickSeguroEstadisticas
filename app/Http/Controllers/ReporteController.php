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
        $reportes = $request->user()->reportes()->with('url')->latest('id')->limit(10)->get();
        return view('reportes.create', compact('reportes'));
    }

    public function store(ReporteRequest $request, UrlService $urls, AnalisisService $servicio)
    {
        try {
            $url = $servicio->registrarUrl($urls->normalizar($request->url));
        } catch (AnalisisException $e) {
            return back()->withInput()->withErrors(['url' => $e->getMessage()]);
        }
        if ($request->user()->reportes()->where('url_id', $url->id)->exists()) {
            return back()->withInput()->withErrors(['url' => 'Ya reportaste esta URL.']);
        }
        $request->user()->reportes()->create([
            'url_id' => $url->id,
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ]);
        return redirect()->route('reportes.create')->with('ok', 'Reporte registrado. Gracias por ayudar.');
    }
}
