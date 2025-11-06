@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar venta #{{ $venta->id }}</h1>

    <div class="mb-3">
        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}<br>
        <strong>Vendedor:</strong> {{ $venta->vendedor_nombre ?? ($venta->vendedor->nombre ?? 'Venta propia') }}<br>
        <strong>Total venta (ARS):</strong> ${{ number_format($venta->total_venta_ars, 2, ',', '.') }}<br>
        <strong>Estado actual:</strong>
        @if($venta->estado_pago === 'pagado')
        <span class="badge bg-success">Pagado</span>
        @else
        <span class="badge bg-warning text-dark">Pendiente</span>
        @endif
    </div>

    <form action="{{ route('ventas.update', $venta->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Monto pagado (acumulado)</label>
                <input type="number" step="0.01" id="monto_pagado" name="monto_pagado"
                    class="form-control" value="{{ $venta->monto_pagado }}" readonly>
            </div>
            <div class="col-md-4">
                <label>Monto a entregar</label>
                <input type="number" step="0.01" id="monto_entregar" class="form-control" value="0">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Saldo pendiente</label>
                <input type="number" step="0.01" id="saldo_pendiente" name="saldo_pendiente"
                    class="form-control" value="{{ $venta->saldo_pendiente }}">
            </div>
        </div>

        <input type="hidden" id="estado_pago" name="estado_pago" value="{{ $venta->estado_pago }}">


        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const totalInput = document.getElementById('total_venta_ars');
                const pagadoInput = document.getElementById('monto_pagado');
                const entregarInput = document.getElementById('monto_entregar'); // nuevo campo editable
                const pendienteInput = document.getElementById('saldo_pendiente');
                const estadoPagoInput = document.getElementById('estado_pago');

                // 🔹 Cuando cambia el monto entregado
                function actualizarDesdeEntregar() {
                    const total = parseFloat(totalInput.value) || 0;
                    const pagado = parseFloat(pagadoInput.value) || 0;
                    const entregar = parseFloat(entregarInput.value) || 0;

                    const nuevoPagado = pagado + entregar;
                    const nuevoPendiente = Math.max(0, total - nuevoPagado);

                    pagadoInput.value = nuevoPagado.toFixed(2);
                    pendienteInput.value = nuevoPendiente.toFixed(2);
                    estadoPagoInput.value = nuevoPendiente > 0 ? 'pendiente' : 'pagado';
                }

                // 🔹 Si cambia el saldo pendiente manualmente
                function actualizarDesdePendiente() {
                    const total = parseFloat(totalInput.value) || 0;
                    const pendiente = parseFloat(pendienteInput.value) || 0;
                    const pagado = total - pendiente;

                    pagadoInput.value = pagado.toFixed(2);
                    entregarInput.value = 0; // se resetea el campo de entrega
                    estadoPagoInput.value = pendiente > 0 ? 'pendiente' : 'pagado';
                }

                // 🔹 Listeners
                entregarInput.addEventListener('input', actualizarDesdeEntregar);
                pendienteInput.addEventListener('input', actualizarDesdePendiente);

                // Inicializa estado al cargar
                actualizarDesdePendiente();
            });
        </script>




        @endsection