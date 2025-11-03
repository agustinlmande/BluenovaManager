@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva compra</h1>

    <form action="{{ route('compras.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Proveedor</label>
                <input type="text" name="proveedor" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" class="form-control" required>
            </div>
        </div>

        <h5>Productos</h5>

        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario (USD)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="productosBody">
                <tr>
                    <td>
                        <select name="productos[0][id]" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="productos[0][cantidad]" class="form-control" min="1" required></td>
                    <td><input type="number" name="productos[0][precio_unitario_usd]" step="0.01" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar producto</button>
        <br><br>

        <button type="submit" class="btn btn-success">Registrar compra</button>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
let fila = 1;
function agregarFila() {
    const tbody = document.getElementById('productosBody');
    const nueva = document.createElement('tr');
    nueva.innerHTML = `
        <td>
            <select name="productos[${fila}][id]" class="form-control" required>
                <option value="">Seleccionar...</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="productos[${fila}][cantidad]" class="form-control" min="1" required></td>
        <td><input type="number" name="productos[${fila}][precio_unitario_usd]" step="0.01" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
    `;
    tbody.appendChild(nueva);
    fila++;
}
function eliminarFila(btn) {
    btn.closest('tr').remove();
}
</script>
@endsection
