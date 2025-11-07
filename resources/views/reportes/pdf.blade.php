<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte financiero - Bluenova</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }

        header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #4da6ff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        header img {
            height: 50px;
            margin-right: 15px;
        }

        header h1 {
            font-size: 20px;
            color: #004080;
            margin: 0;
        }

        .subtitulo {
            color: #4da6ff;
            margin-top: 5px;
            font-size: 13px;
        }

        h2 {
            color: #004080;
            border-left: 4px solid #4da6ff;
            padding-left: 6px;
            font-size: 16px;
            margin-top: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #eaf4ff;
            color: #004080;
        }

        tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
        }

        .info {
            font-size: 12px;
            margin-bottom: 15px;
            color: #444;
        }
    </style>
</head>

<body>

    <header>
        <img src="{{ public_path('images/logo_bluenova.png') }}" alt="Bluenova Logo">
        <div>
            <h1>Bluenova Import</h1>
            <p class="subtitulo">Gestión y estadísticas financieras</p>
        </div>
    </header>

    {{-- 🔹 Información general --}}
    <div class="info">
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        @if($desde && $hasta)
        <p><strong>Período analizado:</strong> {{ $desde }} → {{ $hasta }}</p>
        @else
        <p><strong>Período analizado:</strong> Todos los registros</p>
        @endif
        <p><strong>Última cotización USD:</strong> {{ $ultimaCotizacion->valor_usd ?? 'Sin datos' }}</p>
    </div>

    {{-- 🔹 Resumen financiero --}}
    <h2>💼 Resumen financiero</h2>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto (ARS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>💵 Total de ventas</td>
                <td>${{ number_format($totalVentas ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>📦 Total de compras</td>
                <td>${{ number_format($totalCompras ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>💸 Pendiente de cobro</td>
                <td>${{ number_format($totalPendiente ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>📈 Ganancia neta real</td>
                <td>${{ number_format($gananciaEstimacion ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>📊 Ingresos manuales</td>
                <td>${{ number_format($totalIngresos ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>📉 Egresos manuales</td>
                <td>${{ number_format($totalEgresos ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                {{-- Mostrar "Saldo actual" solo si no hay filtro o si es el día actual --}}
                @if((!$desde && !$hasta) || ($desde === date('Y-m-d') && (!$hasta || $hasta === date('Y-m-d'))))
            <tr>
                <td><strong>Saldo Actual de Caja</strong></td>
                <td>${{ number_format($saldoActual ?? 0, 2, ',', '.') }}</td>
            </tr>
            @endif

            </tr>
        </tfoot>
    </table>



    {{-- 🔹 Ventas mensuales --}}
    <h2>📈 Ventas mensuales</h2>
    @if($ventasMensuales->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Total (ARS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventasMensuales as $venta)
            <tr>
                <td>{{ $venta->mes }}</td>
                <td>${{ number_format($venta->total_ars, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total general</strong></td>
                <td><strong>${{ number_format($ventasMensuales->sum('total_ars'), 2, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p>No hay ventas registradas en este período.</p>
    @endif

    {{-- 🔹 Ganancia mensual real --}}
    <h2>💰 Ganancia mensual real</h2>
    @if($gananciaMensual->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Ganancia (ARS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gananciaMensual as $g)
            <tr>
                <td>{{ $g->mes }}</td>
                <td>${{ number_format($g->total_ganancia, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total general</strong></td>
                <td><strong>${{ number_format($gananciaMensual->sum('total_ganancia'), 2, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p>No hay registros de ganancias en este período.</p>
    @endif

    {{-- 🔹 Pie de página --}}
    <div class="footer">
        <p>© {{ date('Y') }} Bluenova Import — Reporte generado automáticamente desde el sistema de gestión.</p>
    </div>

</body>

</html>