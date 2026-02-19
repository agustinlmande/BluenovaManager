@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva compra</h1>

    <form action="{{ route('compras.store') }}" method="POST">
        @csrf

        <!-- 🧾 Datos principales -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Proveedor</label>
                <input type="text" name="proveedor" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>¿Aplica IVA?</label>
                <select name="aplica_iva" id="aplica_iva" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
            <div class="col-md-2 d-none" id="div_porcentaje_iva">
                <label>% de IVA</label>
                <select name="porcentaje_iva" class="form-control">
                    <option value="21">21%</option>
                    <option value="10.5">10.5%</option>
                </select>
            </div>
        </div>

        <h5>🛒 Productos</h5>

        <!-- 🧱 Tabla de productos -->
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario (USD)</th>
                    <th>Costo envío (ARS)</th>
                    <th>Costo Base (USD) <small title="Costo + IVA + Envío">ℹ️</small></th>
                    <th>% Ganancia</th>
                    <th>Precio venta (USD)</th>
                    <th>Precio venta (ARS)</th>
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
                    <td><input type="number" name="productos[0][cantidad]" class="form-control cantidad" min="1" required></td>
                    <td><input type="number" name="productos[0][precio_unitario_usd]" step="0.01" class="form-control precio_compra" required></td>
                    <td><input type="number" step="0.01" name="productos[0][envio_ars]" class="form-control envio_ars" value="0"></td>
                    <td><input type="number" class="form-control costo_base_usd bg-light" readonly tabindex="-1"></td>
                    <td><input type="number" step="0.01" name="productos[0][ganancia]" class="form-control ganancia"></td>
                    <td><input type="number" step="0.01" name="productos[0][precio_venta_usd]" class="form-control precio_venta_usd"></td>
                    <td><input type="number" step="0.01" name="productos[0][precio_venta_ars]" class="form-control precio_venta_ars"></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
            </tbody>
        </table>

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
                <td><input type="number" step="0.01" class="form-control precio_compra" required></td>
                <td><input type="number" step="0.01" class="form-control envio_ars" value="0"></td>
                <td><input type="number" class="form-control costo_base_usd bg-light" readonly tabindex="-1"></td>
                <td><input type="number" step="0.01" class="form-control ganancia"></td>
                <td><input type="number" step="0.01" class="form-control precio_venta_usd"></td>
                <td><input type="number" step="0.01" class="form-control precio_venta_ars"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
            </tr>
        </template>

        <!-- 🆕 Formulario rápido para crear producto -->
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
                        <option value="nueva_categoria">➕ Nueva categoría</option>
                    </select>
                </div>
            </div>
            <div class="text-end">
                <button type="button" id="btnGuardarNuevo" class="btn btn-success">Guardar producto</button>
            </div>
        </div>

        <!-- ✅ Botones -->
        <button type="button" class="btn btn-outline-primary mt-3" onclick="agregarFila()">+ Agregar producto</button>
        <br><br>

        <div class="mt-4">
            <label for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                placeholder="Ej: Carga de productos antiguos o devolución parcial..."></textarea>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Registrar compra</button>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<!-- Modal nueva categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear nueva categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Nombre de la categoría</label>
                <input type="text" id="nueva_categoria_nombre" class="form-control" placeholder="Ej: Electrónica">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarCategoria" class="btn btn-success">Guardar categoría</button>
            </div>
        </div>
    </div>
</div>

<!-- ⚙️ Scripts -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let fila = 1;

    // Inicializar buscador en compras
    function initSelect2Compra(row) {
        $(row).find('.select-producto').select2({
            placeholder: 'Escribe para buscar...',
            width: '100%'
        }).on('select2:select', function(e) {
            const valor = e.params.data.id;
            const formNuevo = document.getElementById('nuevoProductoForm');

            // Mantenemos tu lógica de "Crear nuevo"
            if (valor === 'nuevo') {
                formNuevo.classList.remove('d-none');
            } else {
                formNuevo.classList.add('d-none');
            }
        });
    }

    function agregarFila() {
        const tbody = document.getElementById('productosBody');
        const template = document.getElementById('filaProductoTemplate');
        const clone = template.content.cloneNode(true);

        // Creamos la fila real para poder inicializar Select2
        const tr = document.createElement('tr');
        tr.innerHTML = clone.firstElementChild.innerHTML;

        // Asignamos nombres dinámicos
        tr.querySelector('select').setAttribute('name', `productos[${fila}][id]`);
        tr.querySelector('.cantidad').setAttribute('name', `productos[${fila}][cantidad]`);
        tr.querySelector('.precio_compra').setAttribute('name', `productos[${fila}][precio_unitario_usd]`);
        tr.querySelector('.envio_ars').setAttribute('name', `productos[${fila}][envio_ars]`);
        tr.querySelector('.ganancia').setAttribute('name', `productos[${fila}][ganancia]`);
        tr.querySelector('.precio_venta_usd').setAttribute('name', `productos[${fila}][precio_venta_usd]`);
        tr.querySelector('.precio_venta_ars').setAttribute('name', `productos[${fila}][precio_venta_ars]`);

        tbody.appendChild(tr);
        initSelect2Compra(tr); // Iniciar buscador
        fila++;
    }

    function eliminarFila(btn) {
        btn.closest('tr').remove();
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar filas existentes
        document.querySelectorAll('#productosBody tr').forEach(initSelect2Compra);

        const formNuevo = document.getElementById('nuevoProductoForm');
        const selectCategoria = document.getElementById('nuevo_categoria');
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));

        // Guardar nueva categoría
        document.getElementById('btnGuardarCategoria').addEventListener('click', async () => {
            const nombre = document.getElementById('nueva_categoria_nombre').value.trim();
            if (!nombre) return alert('⚠️ Ingrese un nombre.');
            try {
                const res = await fetch("{{ route('categorias.storeAjax') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nombre
                    })
                });
                const result = await res.json();
                if (result.success) {
                    const opt = new Option(result.categoria.nombre, result.categoria.id, true, true);
                    $('#nuevo_categoria').append(opt).trigger('change');
                    modal.hide();
                }
            } catch (err) {
                console.error(err);
            }
        });

        // Guardar producto nuevo (AJAX) y actualizar los buscadores
        document.getElementById('btnGuardarNuevo').addEventListener('click', async () => {
            const data = {
                nombre: document.getElementById('nuevo_nombre').value,
                categoria_id: document.getElementById('nuevo_categoria').value,
                _token: '{{ csrf_token() }}'
            };
            try {
                const res = await fetch("{{ route('productos.storeAjax') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data._token
                    },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    // Agregar el nuevo producto a todos los buscadores de la tabla
                    const selects = $('.select-producto');
                    selects.each(function() {
                        const newOption = new Option(result.producto.nombre, result.producto.id, false, false);
                        $(this).append(newOption).trigger('change');
                    });
                    alert('✅ Producto creado correctamente');
                    formNuevo.classList.add('d-none');
                }
            } catch (err) {
                console.error(err);
            }
        });

        // LÓGICA DE CÁLCULOS (IVA, COSTOS, GANANCIAS)
        const cotizacionInput = document.querySelector('input[name="cotizacion_dolar"]');
        const aplicaIvaSelect = document.getElementById('aplica_iva');
        const porcentajeIvaSelect = document.querySelector('select[name="porcentaje_iva"]');

        function recalcularFila(row) {
            const inputs = ['precio_compra', 'envio_ars', 'ganancia', 'precio_venta_usd', 'precio_venta_ars'].map(c => row.querySelector('.' + c));

            function getValues() {
                let multiplicadorIva = (aplicaIvaSelect?.value === '1') ? (1 + (parseFloat(porcentajeIvaSelect.value) || 0) / 100) : 1;
                return {
                    compraUsd: parseFloat(inputs[0].value) || 0,
                    cotizacion: parseFloat(cotizacionInput.value) || 0,
                    envioArs: parseFloat(inputs[1].value) || 0,
                    ganancia: parseFloat(inputs[2].value) || 0,
                    multiplicadorIva: multiplicadorIva
                };
            }

            function updateCalculos() {
                const v = getValues();
                const costoTotalUsd = (v.compraUsd * v.multiplicadorIva) + (v.cotizacion > 0 ? v.envioArs / v.cotizacion : 0);
                row.querySelector('.costo_base_usd').value = costoTotalUsd.toFixed(2);

                if (v.ganancia >= 0) {
                    const ventaUsd = costoTotalUsd * (1 + v.ganancia / 100);
                    inputs[3].value = ventaUsd.toFixed(2);
                    inputs[4].value = (ventaUsd * v.cotizacion).toFixed(2);
                }
            }

            row.addEventListener('input', updateCalculos);
        }

        document.querySelectorAll('#productosBody tr').forEach(recalcularFila);
        const observer = new MutationObserver(muts => muts.forEach(m => m.addedNodes.forEach(n => n.nodeType === 1 && recalcularFila(n))));
        observer.observe(document.getElementById('productosBody'), {
            childList: true
        });
    });
</script>
@endsection