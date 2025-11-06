<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\Caja;
use App\Models\CotizacionDolar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // 📅 Filtros de fechas
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');

        // 🔹 Ventas mensuales
        $ventasMensuales = Venta::select(
                DB::raw('DATE_FORMAT(fecha, "%Y-%m") as mes'),
                DB::raw('SUM(total_venta_ars) as total_ars')
            )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // 🔹 Top productos
        $productosMasVendidos = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_vendidos'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendidos')
            ->take(5)
            ->with('producto')
            ->get();

        // 🔹 Ventas por vendedor
        $ventasVendedores = DB::table('ventas')
            ->leftJoin('vendedores', 'ventas.vendedor_id', '=', 'vendedores.id')
            ->select(
                DB::raw('COALESCE(vendedores.nombre, "Venta propia") as nombre'),
                DB::raw('SUM(ventas.total_venta_ars) as ventas_sum_total_venta_ars')
            )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('ventas.fecha', [$desde, $hasta]))
            ->groupBy('vendedores.nombre')
            ->orderByDesc('ventas_sum_total_venta_ars')
            ->get();

        // 🔹 Ganancia total real (solo ventas)
        $gananciaTotal = DetalleVenta::when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->sum('ganancia_ars');

        // 🔹 Ganancia mensual real
        $gananciaMensual = DetalleVenta::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(ganancia_ars) as total_ganancia')
            )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // 🔹 Datos de caja (manual + automáticos)
        $movimientosCaja = Caja::when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))->get();

        $totalIngresos = $movimientosCaja->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos  = $movimientosCaja->where('tipo', 'egreso')->sum('monto');

        // El saldo actual lo tomamos del último registro en caja
        $saldoActual = Caja::latest('fecha')->first()->saldo ?? 0;

        // 🔹 Otros totales
        $totalVentas     = Venta::sum('total_venta_ars');
        $totalCompras    = Compra::sum('total_ars');
        $totalPendiente  = Venta::where('estado_pago', 'pendiente')->sum('saldo_pendiente');
        $ultimaCotizacion = CotizacionDolar::latest()->first();

        // 🔹 Ganancia estimada = solo las ganancias registradas
        $gananciaEstimacion = $gananciaTotal;

        return view('reportes.index', compact(
            'ventasMensuales',
            'productosMasVendidos',
            'ventasVendedores',
            'gananciaTotal',
            'gananciaMensual',
            'totalIngresos',
            'totalEgresos',
            'saldoActual',
            'totalCompras',
            'totalVentas',
            'totalPendiente',
            'gananciaEstimacion',
            'ultimaCotizacion',
            'desde',
            'hasta'
        ));
    }
}
