<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@bluenova.com',
            'password' => Hash::make('123456'), // ⚠️ Cambiá esta clave si querés
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@bluenova.com')->delete();
    }
};
