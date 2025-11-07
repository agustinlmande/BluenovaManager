@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva venta</h1>

    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label>Cotización dólar</label>
                <input type="number" step="0.01" name="cotizacion_dolar" id="cotizacion_dolar" class="form-control bg-light" readonly required>
            </div>
            <div class="col-md-3">
                <label>Vendedor (opcional)</label>
                <select name="vendedor_id" id="vendedor_id" class="form-control">
                    <option value="">Venta propia</option>
                    @foreach($vendedores as $v)
                    <option value="{{ $v->id }}" data-comision="{{ $v->comision_por_defecto }}">
                        {{ $v->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>% Comisión vendedor</label>
                <input type="number" step="0.01" name="porcentaje_comision_vendedor"
                    id="porcentaje_comision_vendedor" class="form-control" value="0">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Método de pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
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
                <input type="number" step="0.01" name="costo_envio" id="costo_envio" class="form-control" value="0">
            </div>
        </div>

        <h5>Productos vendidos</h5>
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario (ARS)</th>
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
                            <option value="{{ $producto->id }}"
                                data-cotizacion="{{ $producto->cotizacion_compra ?? 0 }}"
                                data-precio-usd="{{ $producto->precio_venta_usd ?? 0 }}"
                                data-precio-ars="{{ $producto->precio_venta_ars ?? 0 }}"
                                data-ganancia="{{ $producto->porcentaje_ganancia ?? 0 }}"
                                data-stock="{{ $producto->stock ?? 0 }}">
                                {{ $producto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="productos[0][cantidad]" min="1"
                            class="form-control cantidad" required>
                        <small class="text-danger d-none mensaje-stock"></small>
                    </td>
                    <td>
                        <input type="number" name="productos[0][precio_unitario_ars]" step="0.01"
                            class="form-control precio_unitario_ars" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control precio_unitario_usd" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar producto</button>

        <hr>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Total venta (ARS)</label>
                <input type="number" step="0.01" name="total_venta_ars" id="total_venta_ars" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Comisión vendedor (ARS)</label>
                <input type="number" step="0.01" id="comision_vendedor" class="form-control bg-light" readonly>
            </div>
            <div class="col-md-3">
                <label>Monto pagado</label>
                <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" class="form-control" value="0" required>
            </div>
            <div class="col-md-3">
                <label>Saldo pendiente</label>
                <input type="number" step="0.01" name="saldo_pendiente" id="saldo_pendiente" class="form-control" value="0" readonly>
            </div>
            <input type="hidden" name="estado_pago" id="estado_pago" value="pagado">
        </div>

        <br>
        <button class="btn btn-success">Registrar venta</button>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    let fila = 1;

    // ➕ Agregar nueva fila
    function agregarFila() {
        const tbody = document.getElementById('productosBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>
            <select name="productos[${fila}][id]" class="form-control select-producto" required>
                <option value="">Seleccionar...</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id }}"
                        data-cotizacion="{{ $producto->cotizacion_compra ?? 0 }}"
                        data-precio-usd="{{ $producto->precio_venta_usd ?? 0 }}"
                        data-precio-ars="{{ $producto->precio_venta_ars ?? 0 }}"
                        data-ganancia="{{ $producto->porcentaje_ganancia ?? 0 }}"
                        data-stock="{{ $producto->stock ?? 0 }}">
                        {{ $producto->nombre }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="productos[${fila}][cantidad]" min="1" class="form-control cantidad" required></td>
        <td><input type="number" name="productos[${fila}][precio_unitario_ars]" step="0.01" class="form-control precio_unitario_ars" required></td>
        <td><input type="number" step="0.01" class="form-control precio_unitario_usd" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
    `;
        tbody.appendChild(tr);
        attachRowEvents(tr);
        fila++;
    }

    function eliminarFila(btn) {
        btn.closest('tr').remove();
        recalcularTotal();
    }

    // 🚚 Cargar datos del producto
    function cargarDatosProducto(row) {
        const select = row.querySelector('.select-producto');
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;

        const cotizacionProducto = parseFloat(opt.dataset.cotizacion || 0);
        const precioVentaUSD = parseFloat(opt.dataset.precioUsd || 0);
        const precioVentaARS_DB = parseFloat(opt.dataset.precioArs || 0);
        const stock = parseInt(opt.dataset.stock || 0);

        // Si aún no hay cotización global, fijar la del primer producto
        const cotizacionInput = document.getElementById('cotizacion_dolar');
        if (!cotizacionInput.value && cotizacionProducto > 0) {
            cotizacionInput.value = cotizacionProducto.toFixed(2);
        }

        const cotizacionVenta = parseFloat(cotizacionInput.value) || cotizacionProducto;

        // Calcular en base a la cotización del producto, no editable
        let baseUSD = precioVentaUSD;
        if (!baseUSD && precioVentaARS_DB && cotizacionProducto) {
            baseUSD = precioVentaARS_DB / cotizacionProducto;
        }
        row.dataset.baseUsd = baseUSD || 0;

        const precioARS = (baseUSD * cotizacionVenta) || 0;
        row.querySelector('.precio_unitario_usd').value = baseUSD ? baseUSD.toFixed(2) : '';
        row.querySelector('.precio_unitario_ars').value = precioARS ? precioARS.toFixed(2) : '';
        row.querySelector('.cantidad').placeholder = `Stock: ${stock}`;

        validarStock(row);
        recalcularTotal();
    }

    function validarStock(row) {
        const select = row.querySelector('.select-producto');
        const cantidad = row.querySelector('.cantidad');
        const mensaje = row.querySelector('.mensaje-stock');
        if (!select || !cantidad || !mensaje) return;

        const opt = select.options[select.selectedIndex];
        const stock = parseInt(opt?.dataset.stock || 0);
        const valor = parseInt(cantidad.value || 0);

        if (stock && valor > stock) {
            cantidad.classList.add('is-invalid');
            mensaje.textContent = `Solo hay ${stock} unidades disponibles.`;
            mensaje.classList.remove('d-none');
        } else {
            cantidad.classList.remove('is-invalid');
            mensaje.textContent = '';
            mensaje.classList.add('d-none');
        }
    }

    function attachRowEvents(row) {
        const select = row.querySelector('.select-producto');
        const cantidad = row.querySelector('.cantidad');
        const precioARSInput = row.querySelector('.precio_unitario_ars');
        const precioUSDInput = row.querySelector('.precio_unitario_usd');

        if (select) select.addEventListener('change', () => cargarDatosProducto(row));
        if (cantidad) cantidad.addEventListener('input', () => {
            validarStock(row);
            recalcularTotal();
        });
        if (precioARSInput) precioARSInput.addEventListener('input', () => {
            const cotizacionVenta = parseFloat(document.getElementById('cotizacion_dolar').value) || 0;
            const valorARS = parseFloat(precioARSInput.value) || 0;
            if (cotizacionVenta > 0 && precioUSDInput)
                precioUSDInput.value = (valorARS / cotizacionVenta).toFixed(2);
            recalcularTotal();
        });
    }

    // 💰 Calcular total
    function recalcularTotal() {
        let subtotalProductos = 0;
        document.querySelectorAll('#productosBody tr').forEach(row => {
            const cantidad = parseFloat(row.querySelector('.cantidad')?.value) || 0;
            const precio = parseFloat(row.querySelector('.precio_unitario_ars')?.value) || 0;
            subtotalProductos += cantidad * precio;
        });

        // Costo envío (si aplica)
        const tipoEntrega = document.querySelector('select[name="tipo_entrega"]').value;
        let costoEnvio = 0;
        if (tipoEntrega === 'envio') {
            costoEnvio = parseFloat(document.getElementById('costo_envio').value) || 0;
        }

        const comision = calcularComision(subtotalProductos);
        const totalFinal = subtotalProductos - comision + costoEnvio;

        document.getElementById('comision_vendedor').value = comision.toFixed(2);
        document.getElementById('total_venta_ars').value = totalFinal.toFixed(2);
        actualizarSaldo();
    }

    function calcularComision(totalProductos) {
        const porcentaje = parseFloat(document.getElementById('porcentaje_comision_vendedor').value) || 0;
        const vendedor = document.getElementById('vendedor_id').value;
        if (vendedor && porcentaje > 0) {
            return (totalProductos * porcentaje) / 100;
        }
        return 0;
    }

    function actualizarSaldo() {
        const total = parseFloat(document.getElementById('total_venta_ars').value) || 0;
        const pagado = parseFloat(document.getElementById('monto_pagado').value) || 0;
        const saldo = Math.max(0, total - pagado);
        document.getElementById('saldo_pendiente').value = saldo.toFixed(2);
        document.getElementById('estado_pago').value = saldo > 0 ? 'pendiente' : 'pagado';
    }

    function toggleEnvioField() {
        const tipoEntrega = document.querySelector('select[name="tipo_entrega"]').value;
        const envioInput = document.getElementById('costo_envio');
        const envioCol = envioInput.closest('.col-md-4');
        if (tipoEntrega === 'envio') {
            envioCol.style.display = 'block';
        } else {
            envioInput.value = 0;
            envioCol.style.display = 'none';
        }
        recalcularTotal();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#productosBody tr').forEach(attachRowEvents);

        document.getElementById('monto_pagado').addEventListener('input', actualizarSaldo);
        document.getElementById('vendedor_id').addEventListener('change', e => {
            const opt = e.target.options[e.target.selectedIndex];
            const comision = parseFloat(opt.dataset.comision || 0);
            document.getElementById('porcentaje_comision_vendedor').value = comision;

            const tipoEntrega = document.querySelector('select[name="tipo_entrega"]');
            const envioInput = document.getElementById('costo_envio');

            if (e.target.value) {
                tipoEntrega.querySelectorAll('option').forEach(opt => opt.disabled = opt.value !== '');
                tipoEntrega.value = '';
                envioInput.closest('.col-md-4').style.display = 'none';
                envioInput.value = 0;
            } else {
                tipoEntrega.querySelectorAll('option').forEach(opt => opt.disabled = false);
            }
            recalcularTotal();
        });

        document.getElementById('porcentaje_comision_vendedor').addEventListener('input', recalcularTotal);
        document.querySelector('select[name="tipo_entrega"]').addEventListener('change', toggleEnvioField);
        toggleEnvioField();
    });
</script>



@endsection