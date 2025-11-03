@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Compras registradas</h1>

    <a href="{{ route('compras.create') }}" class="btn btn-primary mb-3">Nueva compra</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
                        <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detalle{{ $compra->id }}">
                            Ver detalles
                        </button>
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
</div>
@endsection
