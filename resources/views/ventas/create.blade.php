@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva venta</h1>

    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label>% Comisión</label>
                <input type="number" step="0.01" name="porcentaje_comision_vendedor" id="porcentaje_comision_vendedor" class="form-control" value="0">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Método de pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>¿Facturado?</label>
                <select name="facturado" id="facturado" class="form-control">
                    <option value="0">No (Sin IVA)</option>
                    <option value="1">Sí (Con IVA 21%)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>¿A qué cuenta ingresa?</label>
                <select name="cuenta_id" class="form-control" required>
                    <option value="">Seleccionar cuenta...</option>
                    @foreach($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Tipo de entrega</label>
                <select name="tipo_entrega" class="form-control">
                    <option value="">N/A</option>
                    <option value="envio">Envío</option>
                    <option value="retiro">Retiro</option>
                </select>
            </div>
            <div class="col-md-2" id="col_costo_envio" style="display:none;">
                <label>Costo envío</label>
                <input type="number" step="0.01" name="costo_envio" id="costo_envio" class="form-control" value="0">
            </div>
        </div>

        <h5>Productos vendidos</h5>
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th style="width: 30%;">Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario (ARS)</th>
                    <th>Ganancia Real %</th>
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
                                data-costo-ars="{{ ($producto->precio_compra_ars ?? 0) + ($producto->envio_ars ?? 0) }}"
                                data-stock="{{ $producto->stock ?? 0 }}">
                                {{ $producto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="productos[0][cantidad]" min="1" class="form-control cantidad" required>
                        <small class="text-danger d-none mensaje-stock"></small>
                    </td>
                    <td>
                        <input type="number" name="productos[0][precio_unitario_ars]" step="0.01" class="form-control precio_unitario_ars" required>
                    </td>
                    <td>
                        <input type="text" class="form-control ganancia_fila_porcentaje bg-light" readonly tabindex="-1">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control precio_unitario_usd" readonly tabindex="-1">
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
                <input type="number" step="0.01" id="comision_vendedor" class="form-control bg-light" readonly tabindex="-1">
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

        <div class="mb-3">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"></textarea>
        </div>

        <button class="btn btn-success btn-lg">Registrar venta</button>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-lg">Cancelar</a>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let fila = 1;

    // Función para inicializar el buscador en una fila
    function initSelect2(row) {
        $(row).find('.select-producto').select2({
            placeholder: 'Buscar producto...',
            width: '100%'
        }).on('select2:select', function(e) {
            // Esto asegura que al seleccionar con el buscador, se ejecuten tus cálculos
            cargarDatosProducto(row);
        });
    }

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
                        data-costo-ars="{{ ($producto->precio_compra_ars ?? 0) + ($producto->envio_ars ?? 0) }}"
                        data-stock="{{ $producto->stock ?? 0 }}">
                        {{ $producto->nombre }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="productos[${fila}][cantidad]" min="1" class="form-control cantidad" required></td>
        <td><input type="number" name="productos[${fila}][precio_unitario_ars]" step="0.01" class="form-control precio_unitario_ars" required></td>
        <td><input type="text" class="form-control ganancia_fila_porcentaje bg-light" readonly tabindex="-1"></td>
        <td><input type="number" step="0.01" class="form-control precio_unitario_usd" readonly tabindex="-1"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
    `;
        tbody.appendChild(tr);
        attachRowEvents(tr);
        initSelect2(tr); // <--- Inicializa el buscador en la nueva fila
        fila++;
    }

    function eliminarFila(btn) {
        btn.closest('tr').remove();
        recalcularTotal();
    }

    function cargarDatosProducto(row) {
        const select = row.querySelector('.select-producto');
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;

        const cotizacionProducto = parseFloat(opt.dataset.cotizacion || 0);
        const precioVentaARS_DB = parseFloat(opt.dataset.precioArs || 0);
        const stock = parseInt(opt.dataset.stock || 0);

        const cotizacionInput = document.getElementById('cotizacion_dolar');
        if (!cotizacionInput.value && cotizacionProducto > 0) {
            cotizacionInput.value = cotizacionProducto.toFixed(2);
        }

        const cotizacionVenta = parseFloat(cotizacionInput.value) || cotizacionProducto;

        row.querySelector('.precio_unitario_ars').value = precioVentaARS_DB.toFixed(2);
        if (cotizacionVenta > 0) {
            row.querySelector('.precio_unitario_usd').value = (precioVentaARS_DB / cotizacionVenta).toFixed(2);
        }

        row.querySelector('.cantidad').placeholder = `Stock: ${stock}`;
        validarStock(row);
        recalcularTotal();
    }

    function validarStock(row) {
        const select = row.querySelector('.select-producto');
        const cantidadInput = row.querySelector('.cantidad');
        const mensaje = row.querySelector('.mensaje-stock');
        const opt = select.options[select.selectedIndex];
        const stock = parseInt(opt?.dataset.stock || 0);
        const valor = parseInt(cantidadInput.value || 0);

        if (stock && valor > stock) {
            cantidadInput.classList.add('is-invalid');
            mensaje.textContent = `Stock: ${stock}`;
            mensaje.classList.remove('d-none');
        } else {
            cantidadInput.classList.remove('is-invalid');
            mensaje.classList.add('d-none');
        }
    }

    function attachRowEvents(row) {
        const select = row.querySelector('.select-producto');
        const cantidad = row.querySelector('.cantidad');
        const precioARSInput = row.querySelector('.precio_unitario_ars');

        if (select) select.addEventListener('change', () => cargarDatosProducto(row));
        if (cantidad) cantidad.addEventListener('input', () => {
            validarStock(row);
            recalcularTotal();
        });
        if (precioARSInput) precioARSInput.addEventListener('input', () => {
            const cotizacionVenta = parseFloat(document.getElementById('cotizacion_dolar').value) || 0;
            const valorARS = parseFloat(precioARSInput.value) || 0;
            const precioUSDInput = row.querySelector('.precio_unitario_usd');
            if (cotizacionVenta > 0) precioUSDInput.value = (valorARS / cotizacionVenta).toFixed(2);
            recalcularTotal();
        });
    }

    function recalcularTotal() {
        let subtotalProductos = 0;
        const isFacturado = document.getElementById('facturado').value === '1';

        document.querySelectorAll('#productosBody tr').forEach(row => {
            const cantidad = parseFloat(row.querySelector('.cantidad')?.value) || 0;
            const precioVentaArs = parseFloat(row.querySelector('.precio_unitario_ars')?.value) || 0;
            const select = row.querySelector('.select-producto');
            const opt = select.options[select.selectedIndex];

            if (opt && opt.value) {
                const costoUnitarioArs = parseFloat(opt.dataset.costoArs) || 0;
                const ventaEfectivaUnitario = isFacturado ? (precioVentaArs / 1.21) : precioVentaArs;

                if (costoUnitarioArs > 0) {
                    const gananciaPct = ((ventaEfectivaUnitario - costoUnitarioArs) / costoUnitarioArs) * 100;
                    const field = row.querySelector('.ganancia_fila_porcentaje');
                    field.value = gananciaPct.toFixed(1) + '%';
                    field.style.color = gananciaPct < 0 ? 'red' : 'green';
                }
            }
            subtotalProductos += cantidad * precioVentaArs;
        });

        const tipoEntrega = document.querySelector('select[name="tipo_entrega"]').value;
        const costoEnvio = tipoEntrega === 'envio' ? parseFloat(document.getElementById('costo_envio').value) || 0 : 0;

        const comision = calcularComision(subtotalProductos);
        const totalFinal = subtotalProductos - comision + costoEnvio;

        document.getElementById('comision_vendedor').value = comision.toFixed(2);
        document.getElementById('total_venta_ars').value = totalFinal.toFixed(2);
        actualizarSaldo();
    }

    function calcularComision(totalProductos) {
        const porcentaje = parseFloat(document.getElementById('porcentaje_comision_vendedor').value) || 0;
        return (document.getElementById('vendedor_id').value && porcentaje > 0) ? (totalProductos * porcentaje) / 100 : 0;
    }

    function actualizarSaldo() {
        const total = parseFloat(document.getElementById('total_venta_ars').value) || 0;
        const pagado = parseFloat(document.getElementById('monto_pagado').value) || 0;
        const saldo = Math.max(0, total - pagado);
        document.getElementById('saldo_pendiente').value = saldo.toFixed(2);
        document.getElementById('estado_pago').value = saldo > 0 ? 'pendiente' : 'pagado';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar filas existentes
        document.querySelectorAll('#productosBody tr').forEach(row => {
            attachRowEvents(row);
            initSelect2(row);
        });

        document.getElementById('monto_pagado').addEventListener('input', actualizarSaldo);
        document.getElementById('facturado').addEventListener('change', recalcularTotal);
        document.getElementById('porcentaje_comision_vendedor').addEventListener('input', recalcularTotal);

        document.getElementById('vendedor_id').addEventListener('change', e => {
            const opt = e.target.options[e.target.selectedIndex];
            document.getElementById('porcentaje_comision_vendedor').value = opt.dataset.comision || 0;
            recalcularTotal();
        });

        document.querySelector('select[name="tipo_entrega"]').addEventListener('change', e => {
            document.getElementById('col_costo_envio').style.display = e.target.value === 'envio' ? 'block' : 'none';
            if (e.target.value !== 'envio') document.getElementById('costo_envio').value = 0;
            recalcularTotal();
        });
    });
</script>
@endsection