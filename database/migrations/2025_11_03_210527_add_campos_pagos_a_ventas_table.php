<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('monto_pagado', 12, 2)->default(0)->after('total_venta_ars');
            $table->decimal('saldo_pendiente', 12, 2)->default(0)->after('monto_pagado');
            $table->enum('estado_pago', ['pagado', 'pendiente'])->default('pagado')->after('saldo_pendiente');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['monto_pagado', 'saldo_pendiente', 'estado_pago']);
        });
    }
};
