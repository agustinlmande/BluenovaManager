<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // 🔹 Filtros de fechas
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');

        $queryFechas = Venta::query();
        if ($desde && $hasta) {
            $queryFechas->whereBetween('fecha', [$desde, $hasta]);
        } else {
            $queryFechas->where('fecha', '>=', now()->subMonths(6));
        }

        // 🔹 Ventas mensuales
        $ventasMensuales = $queryFechas
            ->select(
                DB::raw('DATE_FORMAT(fecha, "%Y-%m") as mes'),
                DB::raw('SUM(total_venta_ars) as total_ars')
            )
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // 🔹 Top 5 productos más vendidos
        $productosMasVendidos = DetalleVenta::select(
            'producto_id',
            DB::raw('SUM(cantidad) as total_vendidos')
        )
            ->groupBy('producto_id')
            ->orderByDesc('total_vendidos')
            ->take(5)
            ->get();

        // 🔹 Ventas por vendedor
        $ventasVendedores = DB::table('ventas')
            ->leftJoin('vendedores', 'ventas.vendedor_id', '=', 'vendedores.id')
            ->select(
                DB::raw('COALESCE(vendedores.nombre, "Venta propia") as nombre'),
                DB::raw('SUM(ventas.total_venta_ars) as ventas_sum_total_venta_ars')
            )
            ->when($desde && $hasta, function ($query) use ($desde, $hasta) {
                $query->whereBetween('ventas.fecha', [$desde, $hasta]);
            })
            ->groupBy('vendedores.nombre')
            ->orderByDesc('ventas_sum_total_venta_ars')
            ->get();

        // 🔹 Ganancia total
        $gananciaTotal = DetalleVenta::when($desde && $hasta, function ($query) use ($desde, $hasta) {
            $query->whereBetween('created_at', [$desde, $hasta]);
        })
            ->sum('ganancia_ars');

        // 🔹 Ganancia mensual real
        $gananciaMensual = DetalleVenta::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
            DB::raw('SUM(ganancia_ars) as total_ganancia')
        )
            ->when($desde && $hasta, function ($query) use ($desde, $hasta) {
                $query->whereBetween('created_at', [$desde, $hasta]);
            })
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // 🔹 Totales nuevos
        $totalEnCaja = Venta::where('estado_pago', 'pagado')->sum('monto_pagado');
        $totalPendiente = Venta::where('estado_pago', 'pendiente')->sum('saldo_pendiente');

        return view('reportes.index', compact(
            'ventasMensuales',
            'productosMasVendidos',
            'ventasVendedores',
            'gananciaTotal',
            'gananciaMensual',
            'totalEnCaja',
            'totalPendiente'
        ));
    }
}
