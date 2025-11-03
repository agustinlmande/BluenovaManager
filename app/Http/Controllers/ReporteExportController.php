<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\CotizacionDolar;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteExportController extends Controller
{
    // 📄 Exportar reporte de estadísticas a PDF
    public function exportPdf(Request $request)
    {
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');

        // Ventas mensuales
        $ventasMensuales = Venta::select(
                DB::raw('DATE_FORMAT(fecha, "%Y-%m") as mes'),
                DB::raw('SUM(total_venta_ars) as total_ars')
            )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        // Ganancia mensual
        $gananciaMensual = DetalleVenta::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(ganancia_ars) as total_ganancia')
            )
            ->when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();

        $ultimaCotizacion = CotizacionDolar::latest()->first();

        $pdf = Pdf::loadView('reportes.pdf', compact(
            'ventasMensuales',
            'gananciaMensual',
            'ultimaCotizacion',
            'desde',
            'hasta'
        ));

        return $pdf->download('reporte_estadisticas.pdf');
    }

    // 📊 Exportar a Excel con encabezado de empresa, fecha y rango
    public function exportExcel(Request $request)
    {
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');
        $ultimaCotizacion = CotizacionDolar::latest()->first();

        $ventas = Venta::select('fecha', 'total_venta_ars', 'total_venta_usd', 'metodo_pago')
            ->when($desde && $hasta, fn($q) => $q->whereBetween('fecha', [$desde, $hasta]))
            ->orderBy('fecha', 'asc')
            ->get();

        // Crear archivo Excel manualmente con PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Bluenova');

        // Encabezado de empresa
        $sheet->setCellValue('A1', 'Bluenova Import');
        $sheet->setCellValue('A2', 'Gestión y estadísticas de ventas');
        $sheet->setCellValue('A3', 'Reporte generado: ' . now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A4', $desde && $hasta ? "Período: $desde → $hasta" : "Período: Todos los registros");
        $sheet->setCellValue('A5', 'Cotización USD: ' . ($ultimaCotizacion->valor_usd ?? 'Sin datos'));
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->mergeCells('A4:D4');
        $sheet->mergeCells('A5:D5');

        // Encabezados de tabla
        $sheet->fromArray(['Fecha', 'Total (ARS)', 'Total (USD)', 'Método de pago'], null, 'A7');

        // Datos
        $row = 8;
        foreach ($ventas as $v) {
            $sheet->setCellValue("A{$row}", $v->fecha);
            $sheet->setCellValue("B{$row}", $v->total_venta_ars);
            $sheet->setCellValue("C{$row}", $v->total_venta_usd);
            $sheet->setCellValue("D{$row}", ucfirst($v->metodo_pago ?? 'N/A'));
            $row++;
        }

        // Totales al final
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("B{$row}", '=SUM(B8:B' . ($row - 1) . ')');
        $sheet->setCellValue("C{$row}", '=SUM(C8:C' . ($row - 1) . ')');
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);

        // Formato visual básico
        $sheet->getStyle('A1:A5')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A7:D7')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);

        // Descargar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_ventas_bluenova.xlsx';

        // Guardar temporalmente y devolver descarga
        $tempPath = storage_path($filename);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
