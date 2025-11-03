@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nuevo Producto</h1>

    <form action="{{ route('productos.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nombre del producto</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2"></textarea>
            </div>

            <div class="col-md-3 mb-3">
                <label>Stock inicial</label>
                <input type="number" name="stock" class="form-control" value="0" min="0">
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio compra (USD)</label>
                <input type="number" step="0.01" name="precio_compra_usd" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_compra" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio venta (ARS)</label>
                <input type="number" step="0.01" name="precio_venta_ars" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio venta (USD)</label>
                <input type="number" step="0.01" name="precio_venta_usd" class="form-control">
            </div>

            <div class="col-md-3 mb-3">
                <label>% Ganancia</label>
                <input type="number" step="0.01" name="porcentaje_ganancia" class="form-control">
            </div>

        </div>

        <button class="btn btn-success">Guardar Producto</button>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const compraUsd = document.querySelector('input[name="precio_compra_usd"]');
        const cotizacion = document.querySelector('input[name="cotizacion_compra"]');
        const ventaUsd = document.querySelector('input[name="precio_venta_usd"]');
        const ventaArs = document.querySelector('input[name="precio_venta_ars"]');
        const porcentaje = document.querySelector('input[name="porcentaje_ganancia"]');

        function calcularDesdeARS() {
            const pCompra = compraUsd.value * cotizacion.value;
            if (ventaArs.value && cotizacion.value) {
                ventaUsd.value = (ventaArs.value / cotizacion.value).toFixed(2);
                porcentaje.value = (((ventaArs.value - pCompra) / pCompra) * 100).toFixed(2);
            }
        }

        function calcularDesdeUSD() {
            const pCompra = compraUsd.value * cotizacion.value;
            if (ventaUsd.value && cotizacion.value) {
                ventaArs.value = (ventaUsd.value * cotizacion.value).toFixed(2);
                porcentaje.value = (((ventaArs.value - pCompra) / pCompra) * 100).toFixed(2);
            }
        }

        function calcularDesdePorcentaje() {
            const pCompra = compraUsd.value * cotizacion.value;
            if (porcentaje.value && cotizacion.value) {
                ventaArs.value = (pCompra + (pCompra * porcentaje.value / 100)).toFixed(2);
                ventaUsd.value = (ventaArs.value / cotizacion.value).toFixed(2);
            }
        }

        ventaArs.addEventListener('input', calcularDesdeARS);
        ventaUsd.addEventListener('input', calcularDesdeUSD);
        porcentaje.addEventListener('input', calcularDesdePorcentaje);
    });
</script>

@endsection