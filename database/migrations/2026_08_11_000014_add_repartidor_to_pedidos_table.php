<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('repartidor_id')->nullable()->after('negocio_id')->constrained('usuarios')->nullOnDelete();
            $table->index(['estado', 'repartidor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['estado', 'repartidor_id']);
            $table->dropConstrainedForeignId('repartidor_id');
        });
    }
};
