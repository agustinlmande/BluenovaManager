@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva compra</h1>

    <form action="{{ route('compras.store') }}" method="POST">
        @csrf

        <!-- Datos principales -->
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

        <!-- Tabla de productos -->
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
                        <select name="productos[0][id]" class="form-control select-producto" required>
                            <option value="">Seleccionar...</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                            @endforeach
                            <option value="nuevo">➕ Crear nuevo producto</option>
                        </select>
                    </td>
                    <td><input type="number" name="productos[0][cantidad]" class="form-control" min="1" required></td>
                    <td><input type="number" name="productos[0][precio_unitario_usd]" step="0.01" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
            </tbody>
        </table>

        <!-- Plantilla oculta para nuevas filas -->
        <template id="filaProductoTemplate">
            <tr>
                <td>
                    <select class="form-control select-producto" required>
                        <option value="">Seleccionar...</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                        <option value="nuevo">➕ Crear nuevo producto</option>
                    </select>
                </td>
                <td><input type="number" class="form-control cantidad" min="1" required></td>
                <td><input type="number" step="0.01" class="form-control precio" required></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
            </tr>
        </template>

        <!-- Formulario rápido para crear producto -->
        <div id="nuevoProductoForm" class="mt-4 d-none border rounded p-3 bg-light">
            <h5>Nuevo producto</h5>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label>Nombre</label>
                    <input type="text" id="nuevo_nombre" class="form-control">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Categoría</label>
                    <select id="nuevo_categoria" class="form-control">
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Precio compra (USD)</label>
                    <input type="number" id="nuevo_precio_compra" class="form-control" step="0.01">
                </div>
                <div class="col-md-2 mb-2">
                    <label>Cotización dólar</label>
                    <input type="number" id="nuevo_cotizacion" class="form-control" step="0.01">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label>Precio venta (ARS)</label>
                    <input type="number" id="nuevo_precio_ars" class="form-control" step="0.01">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Stock inicial</label>
                    <input type="number" id="nuevo_stock" class="form-control" value="0">
                </div>
                <div class="col-md-3 mb-2">
                    <label>% Ganancia</label>
                    <input type="number" id="nuevo_ganancia" class="form-control" step="0.01">
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <button type="button" id="btnGuardarNuevo" class="btn btn-success w-100">Guardar producto</button>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <button type="button" class="btn btn-outline-primary mt-3" onclick="agregarFila()">+ Agregar producto</button>
        <br><br>

        <button type="submit" class="btn btn-success">Registrar compra</button>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<!-- Scripts -->
<script>
let fila = 1;

function agregarFila() {
    const tbody = document.getElementById('productosBody');
    const template = document.getElementById('filaProductoTemplate');
    const clone = template.content.cloneNode(true);

    // Nombres dinámicos
    clone.querySelector('select').setAttribute('name', `productos[${fila}][id]`);
    clone.querySelector('.cantidad').setAttribute('name', `productos[${fila}][cantidad]`);
    clone.querySelector('.precio').setAttribute('name', `productos[${fila}][precio_unitario_usd]`);

    tbody.appendChild(clone);
    fila++;
}

function eliminarFila(btn) {
    btn.closest('tr').remove();
}

document.addEventListener('DOMContentLoaded', function() {
    const formNuevo = document.getElementById('nuevoProductoForm');
    const btnGuardar = document.getElementById('btnGuardarNuevo');

    // Mostrar / ocultar formulario rápido
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-producto')) {
            if (e.target.value === 'nuevo') {
                formNuevo.classList.remove('d-none');
            } else {
                formNuevo.classList.add('d-none');
            }
        }
    });

    // Guardar producto por AJAX
    btnGuardar.addEventListener('click', async function() {
        const data = {
            nombre: document.getElementById('nuevo_nombre').value,
            categoria_id: document.getElementById('nuevo_categoria').value,
            precio_compra_usd: document.getElementById('nuevo_precio_compra').value,
            cotizacion_compra: document.getElementById('nuevo_cotizacion').value,
            precio_venta_ars: document.getElementById('nuevo_precio_ars').value,
            porcentaje_ganancia: document.getElementById('nuevo_ganancia').value,
            stock: document.getElementById('nuevo_stock').value,
            _token: '{{ csrf_token() }}'
        };

        try {
            const url = "{{ route('productos.storeAjax') }}";
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data._token
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Error HTTP ' + response.status);

            const result = await response.json();

            if (result.success) {
                const selects = document.querySelectorAll('.select-producto');
                selects.forEach(select => {
                    const option = document.createElement('option');
                    option.value = result.producto.id;
                    option.textContent = result.producto.nombre;
                    select.insertBefore(option, select.lastElementChild);
                    select.value = result.producto.id;
                });
                formNuevo.classList.add('d-none');
                formNuevo.querySelectorAll('input').forEach(i => i.value = '');
                alert('✅ Producto creado correctamente');
            } else {
                alert('❌ Error al crear el producto.');
            }
        } catch (error) {
            console.error('Error al conectar con el servidor:', error);
            alert('⚠️ No se pudo guardar el producto (ver consola).');
        }
    });
});
</script>
@endsection
