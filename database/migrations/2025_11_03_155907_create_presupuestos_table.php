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
       Schema::create('presupuestos', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('cliente_id')->nullable();
    $table->date('fecha');
    $table->decimal('total_ars', 12, 2);
    $table->decimal('total_usd', 12, 2)->nullable();
    $table->text('observaciones')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
