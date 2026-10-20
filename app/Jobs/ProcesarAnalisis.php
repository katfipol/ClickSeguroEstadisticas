<?php

namespace App\Jobs;

use App\Exceptions\AnalisisException;
use App\Exceptions\LimiteApiException;
use App\Models\Analisis;
use App\Services\AnalisisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** HU-18: ejecuta el análisis de una URL en segundo plano. */
class ProcesarAnalisis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public int $timeout = 120;

    public function __construct(public int $analisisId)
    {
    }

    public function handle(AnalisisService $servicio): void
    {
        $analisis = Analisis::with('url')->find($this->analisisId);
        if (!$analisis || $analisis->estado === 'completado') {
            return;
        }
        $analisis->update(['estado' => 'procesando', 'error' => null]);

        try {
            $servicio->procesar($analisis);
        } catch (LimiteApiException $e) {
            if ($this->attempts() < $this->tries) {
                $analisis->update(['estado' => 'pendiente', 'error' => 'Esperando cupo del servicio de análisis…']);
                $this->release(65); // la cuenta gratuita se libera cada minuto
                return;
            }
            $analisis->update(['estado' => 'fallido', 'error' => mb_substr($e->getMessage(), 0, 255)]);
        } catch (AnalisisException $e) {
            $analisis->update(['estado' => 'fallido', 'error' => mb_substr($e->getMessage(), 0, 255)]);
        } catch (\Throwable $e) {
            report($e);
            $analisis->update(['estado' => 'fallido', 'error' => 'Ocurrió un error inesperado al analizar.']);
        }
    }
}
