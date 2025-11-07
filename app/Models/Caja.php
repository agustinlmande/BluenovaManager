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
    ];

    public $timestamps = true; // o false si no tenés created_at
}
