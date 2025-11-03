

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Producto</h1>

    <form action="{{ route('productos.update', $producto) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nombre del producto</label>
                <input type="text" name="nombre" class="form-control" value="{{ $producto->nombre }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control" required>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ $categoria->id == $producto->categoria_id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2">{{ $producto->descripcion }}</textarea>
            </div>

            <div class="col-md-3 mb-3">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" value="{{ $producto->stock }}" min="0">
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio compra (USD)</label>
                <input type="number" step="0.01" name="precio_compra_usd" class="form-control" value="{{ $producto->precio_compra_usd }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_compra" class="form-control" value="{{ $producto->cotizacion_compra }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio venta (ARS)</label>
                <input type="number" step="0.01" name="precio_venta_ars" class="form-control" value="{{ $producto->precio_venta_ars }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Precio venta (USD)</label>
                <input type="number" step="0.01" name="precio_venta_usd" class="form-control" value="{{ $producto->precio_venta_usd }}">
            </div>

            <div class="col-md-3 mb-3">
                <label>% Ganancia</label>
                <input type="number" step="0.01" name="porcentaje_ganancia" class="form-control" value="{{ $producto->porcentaje_ganancia }}">
            </div>

            <div class="col-md-3 mb-3">
                <label>Modo de cálculo</label>
                <select name="modo_calculo" class="form-control" required>
                    <option value="porcentaje" {{ $producto->modo_calculo == 'porcentaje' ? 'selected' : '' }}>Por porcentaje</option>
                    <option value="manual" {{ $producto->modo_calculo == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
        </div>

        <button class="btn btn-success">Actualizar Producto</button>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
