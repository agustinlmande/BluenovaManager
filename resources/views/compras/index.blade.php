@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Compras registradas</h1>

    <a href="{{ route('compras.create') }}" class="btn btn-primary mb-3">Nueva compra</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 🔍 FILTROS --}}
    <form action="{{ route('compras.index') }}" method="GET" class="card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label>Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Proveedor</label>
                <input type="text" name="proveedor" value="{{ request('proveedor') }}" class="form-control" placeholder="Proveedor...">
            </div>
        </div>

        <hr>

        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label>Monto USD (mín)</label>
                <input type="number" step="0.01" name="monto_usd_min" value="{{ request('monto_usd_min') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Monto USD (máx)</label>
                <input type="number" step="0.01" name="monto_usd_max" value="{{ request('monto_usd_max') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Monto ARS (mín)</label>
                <input type="number" step="0.01" name="monto_ars_min" value="{{ request('monto_ars_min') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Monto ARS (máx)</label>
                <input type="number" step="0.01" name="monto_ars_max" value="{{ request('monto_ars_max') }}" class="form-control">
            </div>
        </div>

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-outline-primary me-2">Filtrar</button>
            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    {{-- 📋 TABLA DE COMPRAS --}}
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Total (USD)</th>
                <th>Total (ARS)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $compra)
            <tr>
                <td>{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                <td>{{ $compra->proveedor ?? '-' }}</td>
                <td>U$D {{ number_format($compra->total_usd, 2, ',', '.') }}</td>
                <td>$ {{ number_format($compra->total_ars, 2, ',', '.') }}</td>
                <td>
                    <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detalle{{ $compra->id }}">Ver detalles</button>
                    <a href="{{ route('compras.edit', $compra) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('compras.destroy', $compra) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar compra?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <tr class="collapse" id="detalle{{ $compra->id }}">
                <td colspan="5">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio USD</th>
                                <th>Subtotal USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>{{ number_format($detalle->precio_unitario_usd, 2, ',', '.') }}</td>
                                <td>{{ number_format($detalle->precio_unitario_usd * $detalle->cantidad, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 📊 RESUMEN --}}
    @if($compras->count() > 0)
    <div class="card mt-4">
        <div class="card-body text-end">
            <h5 class="fw-semibold">Totales de las compras filtradas:</h5>
            <p class="mb-1">💵 <strong>Total USD:</strong> U$D {{ number_format($totalUsd, 2, ',', '.') }}</p>
            <p>💰 <strong>Total ARS:</strong> $ {{ number_format($totalArs, 2, ',', '.') }}</p>
        </div>
    </div>
    @endif
</div>
@endsection