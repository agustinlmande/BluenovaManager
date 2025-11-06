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

        {{-- total oculto para calculos --}}
        <input type="hidden" id="total_venta_ars" value="{{ $venta->total_venta_ars }}">

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Monto pagado (acumulado)</label>
                <input
                    type="number"
                    step="0.01"
                    id="monto_pagado"
                    name="monto_pagado"
                    class="form-control"
                    value="{{ $venta->monto_pagado }}"
                    readonly
                >
            </div>

            <div class="col-md-4">
                <label>Monto a entregar</label>
                <input
                    type="number"
                    step="0.01"
                    id="monto_entregar"
                    class="form-control"
                    value="0"
                >
            </div>

            <div class="col-md-4">
                <label>Saldo pendiente</label>
                <input
                    type="number"
                    step="0.01"
                    id="saldo_pendiente"
                    name="saldo_pendiente"
                    class="form-control"
                    value="{{ $venta->saldo_pendiente }}"
                >
            </div>
        </div>

        <input type="hidden" id="estado_pago" name="estado_pago" value="{{ $venta->estado_pago }}">

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i></i> Actualizar
            </button>
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                <i></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const totalInput      = document.getElementById('total_venta_ars');
    const pagadoInput     = document.getElementById('monto_pagado');
    const entregarInput   = document.getElementById('monto_entregar');
    const pendienteInput  = document.getElementById('saldo_pendiente');
    const estadoPagoInput = document.getElementById('estado_pago');

    const total        = parseFloat(totalInput.value) || 0;
    let pagadoBase     = parseFloat(pagadoInput.value) || 0; // monto pagado original al entrar a la pantalla

    function actualizarDesdeEntregar() {
        const entregar = parseFloat(entregarInput.value) || 0;

        // siempre calculamos sobre el pagado ORIGINAL, no sobre el que vamos cambiando
        let nuevoPagado    = pagadoBase + entregar;
        if (nuevoPagado > total) {
            nuevoPagado = total;
        }

        const nuevoPendiente = Math.max(0, total - nuevoPagado);

        pagadoInput.value    = nuevoPagado.toFixed(2);
        pendienteInput.value = nuevoPendiente.toFixed(2);
        estadoPagoInput.value = nuevoPendiente > 0 ? 'pendiente' : 'pagado';
    }

    function actualizarDesdePendiente() {
        const pendiente = parseFloat(pendienteInput.value) || 0;

        let nuevoPendiente = pendiente;
        if (nuevoPendiente < 0) nuevoPendiente = 0;
        if (nuevoPendiente > total) nuevoPendiente = total;

        const nuevoPagado = Math.max(0, total - nuevoPendiente);

        pagadoInput.value    = nuevoPagado.toFixed(2);
        entregarInput.value  = 0; // reset del campo de entrega
        estadoPagoInput.value = nuevoPendiente > 0 ? 'pendiente' : 'pagado';

        // si el usuario decide fijar el saldo manualmente,
        // actualizamos la base para futuras entregas
        pagadoBase = nuevoPagado;
        pendienteInput.value = nuevoPendiente.toFixed(2);
    }

    entregarInput.addEventListener('input', actualizarDesdeEntregar);
    pendienteInput.addEventListener('input', actualizarDesdePendiente);
});
</script>
@endsection
