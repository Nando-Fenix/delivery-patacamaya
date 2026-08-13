<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('repartidor_latitud', 10, 7)->nullable()->after('longitud');
            $table->decimal('repartidor_longitud', 10, 7)->nullable()->after('repartidor_latitud');
            $table->decimal('repartidor_precision', 8, 2)->nullable()->after('repartidor_longitud');
            $table->timestamp('ubicacion_repartidor_actualizada_en')->nullable()->after('repartidor_precision');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['repartidor_latitud', 'repartidor_longitud', 'repartidor_precision', 'ubicacion_repartidor_actualizada_en']);
        });
    }
};
