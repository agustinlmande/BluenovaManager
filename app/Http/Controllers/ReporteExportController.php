<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\Caja;
use App\Models\CotizacionDolar;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteExportController extends Controller
{
    // 📄 Exportar reporte financiero a PDF
    public function exportPdf(Request $request)
    {
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

        // 🔹 Ganancia mensual
        $gananciaMensual = DetalleVenta::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
            DB::raw('SUM(ganancia_ars) as total_ganancia')
        )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // 🔹 Totales reales (respetan filtros)
        $totalCompras = Compra::when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))->sum('total_ars');
        $totalVentas  = Venta::when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))->sum('total_venta_ars');

        // 🔹 Caja
        $cajaFiltrada = Caja::when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))->get();
        $totalIngresos = $cajaFiltrada->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos  = $cajaFiltrada->where('tipo', 'egreso')->sum('monto');

        // 🔹 Saldo actual (último valor real de la caja)
        $saldoActual = Caja::latest('fecha')->value('saldo_actual') ?? 0;

        // 🔹 Pendientes de cobro
        $totalPendiente = Venta::when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))
            ->where('estado_pago', 'pendiente')
            ->sum('saldo_pendiente');

        // 🔹 Ganancia total y estimada
        $gananciaTotal = DetalleVenta::when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))->sum('ganancia_ars');
        $gananciaEstimacion = $gananciaTotal; // sin restar compras, se calcula real

        // 🔹 Cotización
        $ultimaCotizacion = CotizacionDolar::latest()->first();

        // 🔹 Datos para la vista PDF
        $pdf = Pdf::loadView('reportes.pdf', compact(
            'ventasMensuales',
            'gananciaMensual',
            'ultimaCotizacion',
            'desde',
            'hasta',
            'totalVentas',
            'totalCompras',
            'totalPendiente',
            'totalIngresos',
            'totalEgresos',
            'saldoActual',
            'gananciaEstimacion'
        ));

        return $pdf->download('Reporte_Financiero_Bluenova_' . now()->format('Ymd_His') . '.pdf');
    }
}
