@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar compra</h1>

    <form action="{{ route('compras.update', $compra->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Proveedor</label>
                <input type="text" name="proveedor" class="form-control" value="{{ $compra->proveedor }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="fecha">Fecha de compra</label>
                <input type="date" name="fecha" id="fecha" class="form-control"
                    value="{{ $compra->fecha }}" max="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-4">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" class="form-control"
                    value="{{ $compra->detalles->first()->cotizacion_dolar ?? '' }}" required>
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
                @foreach($compra->detalles as $i => $detalle)
                <tr>
                    <td>
                        <select name="productos[{{ $i }}][id]" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" {{ $producto->id == $detalle->producto_id ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="productos[{{ $i }}][cantidad]" class="form-control"
                            value="{{ $detalle->cantidad }}" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="productos[{{ $i }}][precio_unitario_usd]" step="0.01"
                            class="form-control" value="{{ $detalle->precio_unitario_usd }}" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar producto</button>
        <br><br>

        <button type="submit" class="btn btn-success">Actualizar compra</button>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    // Contamos cuántas filas hay actualmente en la tabla al cargar la página
    let fila = document.querySelectorAll('#productosBody tr').length;

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