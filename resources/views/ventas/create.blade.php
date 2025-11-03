@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva venta</h1>

    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Vendedor (opcional)</label>
                <select name="vendedor_id" class="form-control">
                    <option value="">Venta propia</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}">{{ $v->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Método de pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Tipo de entrega</label>
                <select name="tipo_entrega" class="form-control">
                    <option value="">N/A</option>
                    <option value="envio">Envío</option>
                    <option value="retiro">Retiro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Costo de envío</label>
                <input type="number" step="0.01" name="costo_envio" class="form-control" value="0">
            </div>
        </div>

        <h5>Productos vendidos</h5>
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario (ARS)</th>
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
                    <td><input type="number" name="productos[0][cantidad]" min="1" class="form-control" required></td>
                    <td><input type="number" name="productos[0][precio_unitario_ars]" step="0.01" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar producto</button>

        <br><br>
        <button class="btn btn-success">Registrar venta</button>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
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
        <td><input type="number" name="productos[${fila}][cantidad]" min="1" class="form-control" required></td>
        <td><input type="number" name="productos[${fila}][precio_unitario_ars]" step="0.01" class="form-control" required></td>
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
