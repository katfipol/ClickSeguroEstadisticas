<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url', function (Blueprint $tabla) {
            $tabla->id('id_url');

            $tabla->foreignId('id_dominio')
                ->constrained('dominio', 'id_dominio')
                ->restrictOnDelete();

            $tabla->string('url', 2048);
            // El hash permite comprobar unicidad sin indexar la dirección completa.
            $tabla->char('url_hash', 64)->unique();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url');
    }
};