<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;

class DashboardController extends Controller
{
    public function index()
    {
        $totalGanado = DetalleVenta::sum('ganancia_ars');
        $totalEnCaja = Venta::where('estado_pago', 'pagado')->sum('monto_pagado');
        $pendienteCobro = Venta::where('estado_pago', 'pendiente')->sum('saldo_pendiente');

        return view('dashboard.index', compact('totalGanado', 'totalEnCaja', 'pendienteCobro'));
    }
}
