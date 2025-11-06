<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('compra_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('precio_compra_usd', 10, 2);
            $table->decimal('cotizacion', 10, 2);
            $table->decimal('precio_compra_ars', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};
