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
        Schema::table('productos', function (Blueprint $table) {
            // Eliminamos la columna modo_calculo si existe
            if (Schema::hasColumn('productos', 'modo_calculo')) {
                $table->dropColumn('modo_calculo');
            }

            // Aseguramos que los campos de venta puedan ser nulos
            $table->decimal('precio_venta_usd', 10, 2)->nullable()->change();
            $table->decimal('precio_venta_ars', 10, 2)->nullable()->change();
            $table->decimal('porcentaje_ganancia', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Volvemos a agregar la columna modo_calculo si se revierte
            $table->enum('modo_calculo', ['porcentaje', 'manual'])->default('porcentaje');
        });
    }
};
