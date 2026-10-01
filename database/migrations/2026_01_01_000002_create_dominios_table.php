<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dominios', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 255)->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dominios');
    }
};
