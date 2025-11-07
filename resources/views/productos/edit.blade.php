@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar producto</h1>

    <form action="{{ route('productos.update', $producto->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 🧾 Datos informativos (solo lectura) --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Nombre del artículo</label>
                <input type="text" class="form-control" value="{{ $producto->nombre }}" readonly>
            </div>

            <div class="col-md-4">
                <label>Categoría</label>
                <input type="text" class="form-control"
                    value="{{ $producto->categoria->nombre ?? 'Sin categoría' }}" readonly>
            </div>

            <div class="col-md-4">
                <label>Proveedor</label>
                <input type="text" class="form-control"
                    value="{{ $producto->ultimoProveedor->proveedor ?? 'Sin proveedor' }}" readonly>
            </div>
        </div>

        {{-- 💲 Campos editables --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" id="cotizacion_dolar" name="cotizacion_dolar"
                    class="form-control" value="{{ old('cotizacion_dolar', $producto->cotizacion_compra ?? 0) }}" required>
            </div>

            <div class="col-md-4">
                <label>Precio unitario (USD)</label>
                <input type="number" step="0.01" id="precio_unitario_usd" name="precio_unitario_usd"
                    class="form-control" value="{{ old('precio_unitario_usd', $producto->precio_compra_usd ?? 0) }}" required>
            </div>

            <div class="col-md-4">
                <label>Costo envío (ARS)</label>
                <input type="number" step="0.01" id="costo_envio_ars" name="costo_envio_ars"
                    class="form-control" value="0" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>% Ganancia</label>
                <input type="number" step="0.01" id="ganancia_porcentaje" name="ganancia_porcentaje"
                    class="form-control" value="{{ old('ganancia_porcentaje', $producto->porcentaje_ganancia ?? 0) }}">
            </div>

            <div class="col-md-4">
                <label>Precio venta (USD)</label>
                <input type="number" step="0.01" id="precio_venta_usd" name="precio_venta_usd"
                    class="form-control" value="{{ old('precio_venta_usd', $producto->precio_venta_usd ?? 0) }}">
            </div>

            <div class="col-md-4">
                <label>Precio venta (ARS)</label>
                <input type="number" step="0.01" id="precio_venta_ars" name="precio_venta_ars"
                    class="form-control" value="{{ old('precio_venta_ars', $producto->precio_venta_ars ?? 0) }}">
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

{{-- === SCRIPT DE CÁLCULOS === --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cotizacion = document.getElementById('cotizacion_dolar');
        const unitarioUsd = document.getElementById('precio_unitario_usd');
        const envio = document.getElementById('costo_envio_ars');
        const ganancia = document.getElementById('ganancia_porcentaje');
        const ventaUsd = document.getElementById('precio_venta_usd');
        const ventaArs = document.getElementById('precio_venta_ars');

        // === Calcular desde Ganancia ===
        function recalcularDesdeGanancia() {
            const c = parseFloat(cotizacion.value) || 0;
            const u = parseFloat(unitarioUsd.value) || 0;
            const e = parseFloat(envio.value) || 0;
            const g = parseFloat(ganancia.value) || 0;

            const envioUSD = c > 0 ? e / c : 0;
            const ventaUSD = (u + envioUSD) * (1 + g / 100);
            const ventaARS = ventaUSD * c;

            ventaUsd.value = ventaUSD.toFixed(2);
            ventaArs.value = ventaARS.toFixed(2);
        }

        // === Calcular desde Precio USD ===
        function recalcularDesdeVentaUsd() {
            const c = parseFloat(cotizacion.value) || 0;
            const u = parseFloat(unitarioUsd.value) || 0;
            const e = parseFloat(envio.value) || 0;
            const vUSD = parseFloat(ventaUsd.value) || 0;
            const envioUSD = c > 0 ? e / c : 0;

            const g = ((vUSD / (u + envioUSD)) - 1) * 100;
            const vARS = vUSD * c;

            ganancia.value = g.toFixed(2);
            ventaArs.value = vARS.toFixed(2);
        }

        // === Calcular desde Precio ARS ===
        function recalcularDesdeVentaArs() {
            const c = parseFloat(cotizacion.value) || 0;
            const u = parseFloat(unitarioUsd.value) || 0;
            const e = parseFloat(envio.value) || 0;
            const vARS = parseFloat(ventaArs.value) || 0;

            if (c > 0) {
                const vUSD = vARS / c;
                const envioUSD = e / c;
                const g = ((vUSD / (u + envioUSD)) - 1) * 100;

                ventaUsd.value = vUSD.toFixed(2);
                ganancia.value = g.toFixed(2);
            }
        }

        // === Eventos dinámicos ===
        ganancia.addEventListener('input', recalcularDesdeGanancia);
        ventaUsd.addEventListener('input', recalcularDesdeVentaUsd);
        ventaArs.addEventListener('input', recalcularDesdeVentaArs);
        cotizacion.addEventListener('input', recalcularDesdeGanancia);
        unitarioUsd.addEventListener('input', recalcularDesdeGanancia);
        envio.addEventListener('input', recalcularDesdeGanancia);
    });
</script>
@endsection