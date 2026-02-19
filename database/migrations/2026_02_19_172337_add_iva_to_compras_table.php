<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            // Agregamos el campo booleano (si o no) y el porcentaje (ej: 21.00 o 10.50)
            $table->boolean('aplica_iva')->default(false)->after('observaciones');
            $table->decimal('porcentaje_iva', 5, 2)->nullable()->after('aplica_iva');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['aplica_iva', 'porcentaje_iva']);
        });
    }
};
