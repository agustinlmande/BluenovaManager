@extends('layouts.app')

@section('content')
@php
// Variables seguras por defecto
$__etiquetasVentas = ($ventasMensuales ?? collect())->pluck('mes')->toArray();
$__datosVentas = ($ventasMensuales ?? collect())->pluck('total_ars')->toArray();
$__etiquetasVendedores = ($ventasVendedores ?? collect())->pluck('nombre')->toArray();
$__datosVendedores = ($ventasVendedores ?? collect())->pluck('ventas_sum_total_venta_ars')->toArray();
$__etiquetasGanancias = ($gananciaMensual ?? collect())->pluck('mes')->toArray();
$__datosGanancias = ($gananciaMensual ?? collect())->pluck('total_ganancia')->toArray();
@endphp

<div class="container">
    <h1>Dashboard de estadísticas</h1>

    <!-- 🔹 Filtros de fechas + exportaciones -->
    <form method="GET" action="{{ route('reportes.index') }}" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
        </div>
        <div class="col-md-3">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Generar reporte
            </button>
        </div>
        <div class="col-md-3 text-end">
            <div class="d-flex justify-content-end flex-wrap gap-2">
                <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar filtros
                </a>
                <a href="{{ route('reportes.export.pdf') }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('reportes.export.excel') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
    </form>

    <hr>

    <!-- 🔹 Tarjetas resumen -->
    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h5>Total Ganado</h5>
                <h3 class="text-success">${{ number_format($gananciaTotal, 2, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h5>Última cotización dólar</h5>
                <h3>{{ $ultimaCotizacion->valor_usd ?? 'Sin datos' }}</h3>
            </div>
        </div>

        <!-- 💵 NUEVAS TARJETAS -->
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h5>💵 Total en caja</h5>
                <h3 class="text-success">${{ number_format($totalEnCaja ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h5>🧾 Pendiente de cobro</h5>
                <h3 class="text-danger">${{ number_format($totalPendiente ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- 🔹 Gráficos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm p-3 mb-4">
                <h5>Ventas mensuales</h5>
                <div style="height: 350px;">
                    <canvas id="chartVentas"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 mb-4">
                <h5>Top 5 productos más vendidos</h5>
                <ul class="list-group">
                    @foreach($productosMasVendidos as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $p->producto->nombre ?? 'N/A' }}
                        <span class="badge bg-primary rounded-pill">{{ $p->total_vendidos }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3">
        <h5>Ventas por vendedor</h5>
        <div style="height: 350px;">
            <canvas id="chartVendedores"></canvas>
        </div>
    </div>

    <div class="card shadow-sm p-3 mt-4">
        <h5>Ganancia mensual real</h5>
        <div style="height: 350px;">
            <canvas id="chartGanancias"></canvas>
        </div>
    </div>
</div>

<!-- 🔹 Datos para los gráficos -->
<div id="report-data"
    data-etiquetas-ventas='@json($__etiquetasVentas)'
    data-datos-ventas='@json($__datosVentas)'
    data-etiquetas-vendedores='@json($__etiquetasVendedores)'
    data-datos-vendedores='@json($__datosVendedores)'
    data-etiquetas-ganancias='@json($__etiquetasGanancias)'
    data-datos-ganancias='@json($__datosGanancias)'>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        const dataEl = document.getElementById('report-data');

        // === Ventas Mensuales ===
        const etiquetasVentas = JSON.parse(dataEl.getAttribute('data-etiquetas-ventas') || '[]');
        const datosVentas = JSON.parse(dataEl.getAttribute('data-datos-ventas') || '[]');
        const ctxVentas = document.getElementById('chartVentas')?.getContext('2d');

        if (ctxVentas && etiquetasVentas.length > 0) {
            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: etiquetasVentas,
                    datasets: [{
                        label: 'Ventas (ARS)',
                        data: datosVentas,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.3)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '$' + v.toLocaleString('es-AR')
                            }
                        }
                    }
                }
            });
        }

        // === Ventas por Vendedor ===
        const etiquetasVendedores = JSON.parse(dataEl.getAttribute('data-etiquetas-vendedores') || '[]');
        const datosVendedores = JSON.parse(dataEl.getAttribute('data-datos-vendedores') || '[]');
        const ctxVendedores = document.getElementById('chartVendedores')?.getContext('2d');

        if (ctxVendedores && etiquetasVendedores.length > 0) {
            new Chart(ctxVendedores, {
                type: 'bar',
                data: {
                    labels: etiquetasVendedores,
                    datasets: [{
                        label: 'Ventas por vendedor (ARS)',
                        data: datosVendedores,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                        ],
                        borderColor: 'rgba(0,0,0,0.2)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '$' + v.toLocaleString('es-AR')
                            }
                        }
                    }
                }
            });
        }

        // === Ganancia Mensual ===
        const etiquetasGanancias = JSON.parse(dataEl.getAttribute('data-etiquetas-ganancias') || '[]');
        const datosGanancias = JSON.parse(dataEl.getAttribute('data-datos-ganancias') || '[]');
        const ctxGanancias = document.getElementById('chartGanancias')?.getContext('2d');

        if (ctxGanancias && etiquetasGanancias.length > 0) {
            new Chart(ctxGanancias, {
                type: 'line',
                data: {
                    labels: etiquetasGanancias,
                    datasets: [{
                        label: 'Ganancia mensual (ARS)',
                        data: datosGanancias,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.3)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '$' + v.toLocaleString('es-AR')
                            }
                        }
                    }
                }
            });
        }
    })();
</script>
@endsection