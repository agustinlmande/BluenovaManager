<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';

    protected $fillable = ['nombre', 'saldo'];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cuenta_id');
    }
}
