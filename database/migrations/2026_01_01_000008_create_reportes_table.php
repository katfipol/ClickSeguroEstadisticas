<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->foreignId('url_id')->constrained('urls')->restrictOnDelete();
            $t->string('motivo', 500)->nullable();
            $t->string('estado', 20)->default('pendiente'); // pendiente | revisado | descartado
            $t->timestamps();
            $t->unique(['usuario_id', 'url_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
