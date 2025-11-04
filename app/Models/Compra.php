<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = ['proveedor', 'fecha', 'total_usd', 'total_ars', 'observaciones'];

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}
