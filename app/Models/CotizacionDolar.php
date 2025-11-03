<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionDolar extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_dolars';

    protected $fillable = ['valor_usd', 'fecha'];
}
