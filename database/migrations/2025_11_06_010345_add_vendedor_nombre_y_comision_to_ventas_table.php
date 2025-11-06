<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Nombre del vendedor “congelado” en la venta
            $table->string('vendedor_nombre')->nullable()->after('vendedor_id');

            // % de comisión usado en ESA venta (puede diferir del defecto)
            $table->decimal('porcentaje_comision_vendedor', 5, 2)->nullable()->after('vendedor_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['vendedor_nombre', 'porcentaje_comision_vendedor']);
        });
    }
};
