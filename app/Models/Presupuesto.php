<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;

    protected $table = 'presupuestos';

    protected $fillable = [
        'cliente_id', 'fecha', 'total_ars', 'total_usd', 'observaciones'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePresupuesto::class, 'presupuesto_id');
    }
}
