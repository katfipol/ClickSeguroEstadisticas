<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nota: make:model Analisis -m genera "analises"; aquí se corrige a "analisis".
        Schema::create('analisis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->foreignId('url_id')->constrained('urls')->restrictOnDelete();
            $t->foreignId('url_final_id')->nullable()->constrained('urls')->restrictOnDelete();
            $t->foreignId('tipo_riesgo_id')->constrained('tipo_riesgos')->restrictOnDelete();
            $t->foreignId('analisis_anterior_id')->nullable()->constrained('analisis')->nullOnDelete();
            $t->timestamp('created_at')->useCurrent(); // el análisis es inmutable: sin updated_at
            $t->index(['usuario_id', 'created_at']);
            $t->index(['usuario_id', 'tipo_riesgo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};
