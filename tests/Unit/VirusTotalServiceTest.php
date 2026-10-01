<?php

namespace Tests\Unit;

use App\Exceptions\AnalisisException;
use App\Services\VirusTotalService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VirusTotalServiceTest extends TestCase
{
    public function test_rechaza_un_analisis_completado_sin_datos(): void
    {
        $this->simularAnalisisCompletado([], []);

        $this->expectException(AnalisisException::class);
        $this->expectExceptionMessage('El servicio de análisis no devolvió datos suficientes. Intenta de nuevo.');

        app(VirusTotalService::class)->analizar('https://example.com');
    }

    public function test_acepta_un_analisis_con_datos_sin_detecciones(): void
    {
        $this->simularAnalisisCompletado([
            'harmless' => 1,
            'malicious' => 0,
            'suspicious' => 0,
        ], [
            'MotorPrueba' => [
                'engine_name' => 'MotorPrueba',
                'category' => 'harmless',
                'result' => 'clean',
            ],
        ]);

        $resultado = app(VirusTotalService::class)->analizar('https://example.com');

        $this->assertSame(0, $resultado['malicious']);
        $this->assertSame(0, $resultado['suspicious']);
        $this->assertSame([], $resultado['hallazgos']);
        Http::assertSentCount(2);
    }

    public function test_conserva_la_deteccion_maliciosa_de_un_motor(): void
    {
        $this->simularAnalisisCompletado([
            'harmless' => 0,
            'malicious' => 1,
            'suspicious' => 0,
        ], [
            'MotorPrueba' => [
                'engine_name' => 'MotorPrueba',
                'category' => 'malicious',
                'result' => 'phishing',
            ],
        ]);

        $resultado = app(VirusTotalService::class)->analizar('https://example.com');

        $this->assertSame(1, $resultado['malicious']);
        $this->assertSame(0, $resultado['suspicious']);
        $this->assertCount(1, $resultado['hallazgos']);
        $this->assertSame('MotorPrueba', $resultado['hallazgos'][0]['motor']);
        $this->assertSame('malicioso', $resultado['hallazgos'][0]['resultado']);
        $this->assertSame('phishing', $resultado['hallazgos'][0]['categoria']);
        Http::assertSentCount(2);
    }

    private function simularAnalisisCompletado(array $estadisticas, array $motores): void
    {
        config([
            'virustotal.api_key' => 'clave-ficticia-para-pruebas',
            'virustotal.base_url' => 'https://virustotal.test/api/v3',
            'virustotal.timeout' => 5,
            'virustotal.max_polls' => 1,
        ]);

        Http::fake([
            'https://virustotal.test/api/v3/urls' => Http::response([
                'data' => ['id' => 'analisis-prueba'],
            ], 200),
            'https://virustotal.test/api/v3/analyses/analisis-prueba' => Http::response([
                'data' => [
                    'attributes' => [
                        'status' => 'completed',
                        'stats' => $estadisticas,
                        'results' => $motores,
                    ],
                ],
            ], 200),
        ]);
    }
}