@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Recibo de venta #{{ $venta->id }}</h1>
        <button class="btn btn-outline-primary" onclick="window.print()">Imprimir</button>
    </div>

    <div class="card p-3">
        <div class="row">
            <div class="col-md-6">
                {{-- Logo, poné tu archivo en public/images/logo_bluenova.png --}}
                <img src="{{ asset('images/logo_bluenova.png') }}" alt="Bluenova" style="max-height: 80px;">
                <p class="mt-2 mb-0"><strong>Bluenova</strong></p>
                <small>Recibo de venta</small>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-1"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</p>
                <p class="mb-1"><strong>Vendedor:</strong> {{ $venta->vendedor_nombre ?? ($venta->vendedor->nombre ?? 'Venta propia') }}</p>
                <p class="mb-1"><strong>Método de pago:</strong> {{ ucfirst($venta->metodo_pago) }}</p>
                <p class="mb-1"><strong>Tipo entrega:</strong> {{ $venta->tipo_entrega ?? 'N/A' }}</p>
            </div>
        </div>

        <hr>

        <h5>Productos</h5>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio unit. (ARS)</th>
                    <th>Subtotal (ARS)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalProductos = 0; @endphp
                @foreach($venta->detalles as $detalle)
                @php
                $subtotal = $detalle->precio_unitario_ars * $detalle->cantidad;
                $totalProductos += $subtotal;
                @endphp
                <tr>
                    <td>{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>${{ number_format($detalle->precio_unitario_ars, 2, ',', '.') }}</td>
                    <td>${{ number_format($subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col-md-6">
                @if($venta->observaciones)
                <p><strong>Observaciones:</strong><br>{{ $venta->observaciones }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th class="text-end">Total productos:</th>
                        <td class="text-end">${{ number_format($totalProductos, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">Costo envío:</th>
                        <td class="text-end">${{ number_format($venta->costo_envio, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">Total venta:</th>
                        <td class="text-end"><strong>${{ number_format($venta->total_venta_ars, 2, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <th class="text-end">Monto pagado:</th>
                        <td class="text-end">${{ number_format($venta->monto_pagado, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">Saldo pendiente:</th>
                        <td class="text-end">
                            @if($venta->saldo_pendiente > 0)
                            <strong>${{ number_format($venta->saldo_pendiente, 2, ',', '.') }}</strong>
                            @else
                            $0,00
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="mt-4 text-center">
            Gracias por su compra.
        </p>
    </div>
</div>

<style>
    @media print {

        nav.navbar,
        .btn,
        a.btn,
        .alert {
            display: none !important;
        }

        body {
            background: #fff;
        }
    }
</style>
@endsection