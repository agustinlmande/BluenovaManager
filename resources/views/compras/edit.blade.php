@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar compra</h1>

    <form action="{{ route('compras.update', $compra->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- 🧾 Datos principales -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Proveedor</label>
                <input type="text" name="proveedor" class="form-control" value="{{ $compra->proveedor }}">
            </div>
            <div class="col-md-4">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $compra->fecha }}" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" class="form-control"
                    value="{{ $compra->detalles->first()->cotizacion_dolar ?? '' }}" required>
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
                    <th>% Ganancia</th>
                    <th>Precio venta (USD)</th>
                    <th>Precio venta (ARS)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="productosBody">
                @foreach($compra->detalles as $i => $detalle)
                <tr>
                    <td>
                        <select name="productos[{{ $i }}][id]" class="form-control select-producto" required>
                            <option value="">Seleccionar...</option>
                            @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" {{ $producto->id == $detalle->producto_id ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                            </option>
                            @endforeach
                            <option value="nuevo">➕ Crear nuevo producto</option>
                        </select>
                    </td>
                    <td><input type="number" name="productos[{{ $i }}][cantidad]" class="form-control cantidad" value="{{ $detalle->cantidad }}" min="1" required></td>
                    <td><input type="number" name="productos[{{ $i }}][precio_unitario_usd]" step="0.01" class="form-control precio_compra" value="{{ $detalle->precio_unitario_usd }}" required></td>
                    <td><input type="number" step="0.01" name="productos[{{ $i }}][envio_ars]" class="form-control envio_ars" value="{{ $detalle->envio_ars ?? 0 }}"></td>
                    <td><input type="number" step="0.01" name="productos[{{ $i }}][ganancia]" class="form-control ganancia"
                            value="{{ $detalle->producto->porcentaje_ganancia ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="productos[{{ $i }}][precio_venta_usd]" class="form-control precio_venta_usd"
                            value="{{ $detalle->producto->precio_venta_usd ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="productos[{{ $i }}][precio_venta_ars]" class="form-control precio_venta_ars"
                            value="{{ $detalle->producto->precio_venta_ars ?? '' }}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Plantilla oculta -->
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
            <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ $compra->observaciones }}</textarea>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Actualizar compra</button>
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
<script>
    let fila = document.querySelectorAll('#productosBody tr').length;

    function agregarFila() {
        const tbody = document.getElementById('productosBody');
        const template = document.getElementById('filaProductoTemplate');
        const clone = template.content.cloneNode(true);
        clone.querySelector('select').setAttribute('name', `productos[${fila}][id]`);
        clone.querySelector('.cantidad').setAttribute('name', `productos[${fila}][cantidad]`);
        clone.querySelector('.precio_compra').setAttribute('name', `productos[${fila}][precio_unitario_usd]`);
        clone.querySelector('.envio_ars').setAttribute('name', `productos[${fila}][envio_ars]`);
        clone.querySelector('.ganancia').setAttribute('name', `productos[${fila}][ganancia]`);
        clone.querySelector('.precio_venta_usd').setAttribute('name', `productos[${fila}][precio_venta_usd]`);
        clone.querySelector('.precio_venta_ars').setAttribute('name', `productos[${fila}][precio_venta_ars]`);
        tbody.appendChild(clone);
        fila++;
    }

    function eliminarFila(btn) {
        btn.closest('tr').remove();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const formNuevo = document.getElementById('nuevoProductoForm');
        const selectCategoria = document.getElementById('nuevo_categoria');
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));

        document.addEventListener('change', e => {
            if (e.target.classList.contains('select-producto')) {
                formNuevo.classList.toggle('d-none', e.target.value !== 'nuevo');
            }
        });

        selectCategoria.addEventListener('change', () => {
            if (selectCategoria.value === 'nueva_categoria') modal.show();
        });

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
                    const opt = document.createElement('option');
                    opt.value = result.categoria.id;
                    opt.textContent = result.categoria.nombre;
                    selectCategoria.appendChild(opt);
                    selectCategoria.value = result.categoria.id;
                    document.getElementById('nueva_categoria_nombre').value = '';
                    modal.hide();
                    alert('✅ Categoría creada correctamente.');
                } else alert('❌ Error al crear la categoría.');
            } catch (err) {
                console.error(err);
                alert('⚠️ Error de conexión.');
            }
        });

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
                    const selects = document.querySelectorAll('.select-producto');
                    const selectActual = Array.from(selects).find(s => s.value === 'nuevo');

                    if (selectActual) {
                        const option = document.createElement('option');
                        option.value = result.producto.id;
                        option.textContent = result.producto.nombre;
                        selectActual.insertBefore(option, selectActual.lastElementChild);
                        selectActual.value = result.producto.id;
                    }

                    alert('✅ Producto creado correctamente');
                    formNuevo.classList.add('d-none');
                    formNuevo.querySelectorAll('input').forEach(i => i.value = '');
                } else {
                    console.error(result.message);
                    alert('❌ Error al crear el producto.');
                }

            } catch (err) {
                console.error(err);
                alert('⚠️ No se pudo guardar el producto.');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const cotizacionInput = document.querySelector('input[name="cotizacion_dolar"]');

        function recalcularFila(row) {
            const compraInput = row.querySelector('.precio_compra');
            const envioInput = row.querySelector('.envio_ars');
            const gananciaInput = row.querySelector('.ganancia');
            const ventaUsdInput = row.querySelector('.precio_venta_usd');
            const ventaArsInput = row.querySelector('.precio_venta_ars');

            function getValues() {
                return {
                    compraUsd: parseFloat(compraInput.value) || 0,
                    cotizacion: parseFloat(cotizacionInput.value) || 0,
                    envioArs: parseFloat(envioInput.value) || 0,
                    ganancia: parseFloat(gananciaInput.value) || 0,
                    ventaUsd: parseFloat(ventaUsdInput.value) || 0,
                    ventaArs: parseFloat(ventaArsInput.value) || 0,
                };
            }

            function calcularDesdeGanancia() {
                const {
                    compraUsd,
                    cotizacion,
                    envioArs,
                    ganancia
                } = getValues();
                if (compraUsd > 0 && cotizacion > 0 && ganancia >= 0) {
                    const envioUsd = envioArs / cotizacion;
                    const costoTotalUsd = compraUsd + envioUsd;
                    const ventaUsd = costoTotalUsd + (costoTotalUsd * ganancia / 100);
                    const ventaArs = ventaUsd * cotizacion;
                    ventaUsdInput.value = ventaUsd.toFixed(2);
                    ventaArsInput.value = ventaArs.toFixed(2);
                }
            }

            function calcularDesdeVentaUsd() {
                const {
                    compraUsd,
                    cotizacion,
                    envioArs,
                    ventaUsd
                } = getValues();
                if (compraUsd > 0 && cotizacion > 0 && ventaUsd > 0) {
                    const envioUsd = envioArs / cotizacion;
                    const costoTotalUsd = compraUsd + envioUsd;
                    const ganancia = ((ventaUsd - costoTotalUsd) / costoTotalUsd) * 100;
                    const ventaArs = ventaUsd * cotizacion;
                    gananciaInput.value = ganancia.toFixed(2);
                    ventaArsInput.value = ventaArs.toFixed(2);
                }
            }

            function calcularDesdeVentaArs() {
                const {
                    compraUsd,
                    cotizacion,
                    envioArs,
                    ventaArs
                } = getValues();
                if (compraUsd > 0 && cotizacion > 0 && ventaArs > 0) {
                    const ventaUsd = ventaArs / cotizacion;
                    const envioUsd = envioArs / cotizacion;
                    const costoTotalUsd = compraUsd + envioUsd;
                    const ganancia = ((ventaUsd - costoTotalUsd) / costoTotalUsd) * 100;
                    gananciaInput.value = ganancia.toFixed(2);
                    ventaUsdInput.value = ventaUsd.toFixed(2);
                }
            }

            gananciaInput.addEventListener('input', calcularDesdeGanancia);
            ventaUsdInput.addEventListener('input', calcularDesdeVentaUsd);
            ventaArsInput.addEventListener('input', calcularDesdeVentaArs);
            compraInput.addEventListener('input', calcularDesdeGanancia);
            envioInput.addEventListener('input', calcularDesdeGanancia);
        }

        document.querySelectorAll('#productosBody tr').forEach(recalcularFila);

        const observer = new MutationObserver(() => {
            document.querySelectorAll('#productosBody tr').forEach(recalcularFila);
        });
        observer.observe(document.getElementById('productosBody'), {
            childList: true
        });

        cotizacionInput.addEventListener('input', () => {
            document.querySelectorAll('#productosBody tr').forEach(row => {
                const event = new Event('input');
                row.querySelector('.ganancia')?.dispatchEvent(event);
            });
        });
    });
</script>
@endsection