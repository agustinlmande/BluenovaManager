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
        Schema::table('caja', function (Blueprint $table) {
            // Relacionamos el movimiento con una cuenta
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropForeign(['cuenta_id']);
            $table->dropColumn('cuenta_id');
        });
    }
};
