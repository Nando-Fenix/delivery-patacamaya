<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones_usuario', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete();
            $t->string('nombre', 100);
            $t->string('direccion_referencia');
            $t->decimal('latitud', 10, 7);
            $t->decimal('longitud', 10, 7);
            $t->boolean('predeterminada')->default(false)->index();
            $t->boolean('activo')->default(true)->index();
            $t->timestamps();
            $t->index(['usuario_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones_usuario');
    }
};
