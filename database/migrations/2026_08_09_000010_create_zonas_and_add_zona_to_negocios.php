<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 100)->unique();
            $t->string('descripcion')->nullable();
            $t->boolean('activo')->default(true)->index();
            $t->unsignedInteger('orden')->nullable();
            $t->timestamps();
        });
        Schema::table('negocios', fn (Blueprint $t) => $t->foreignId('zona_id')->nullable()->after('categoria_negocio_id')->constrained('zonas')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('negocios', fn (Blueprint $t) => $t->dropConstrainedForeignId('zona_id'));
        Schema::dropIfExists('zonas');
    }
};
