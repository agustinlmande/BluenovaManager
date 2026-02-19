<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja'; // o 'caja' según tu migración

    protected $fillable = [
        'tipo',
        'monto',
        'motivo',
        'fecha',
        'editable',
        'saldo_actual',
        'cuenta_id', // <-- NUEVO
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
