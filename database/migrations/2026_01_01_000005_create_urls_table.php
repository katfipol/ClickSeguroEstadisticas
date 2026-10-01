<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dominio_id')->constrained('dominios')->restrictOnDelete();
            $t->string('url', 2048);
            $t->char('url_hash', 64)->unique(); // SHA-256 de url: clave de unicidad (MySQL no indexa VARCHAR(2048) completo)
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
