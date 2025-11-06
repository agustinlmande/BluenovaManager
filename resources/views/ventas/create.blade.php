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
                <input type="number" step="0.01" name="cotizacion_dolar" id="cotizacion_dolar" class="form-control" required>
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
                                data-precio-ars="{{ $producto->precio_venta_ars }}">
                                {{ $producto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="productos[0][cantidad]" min="1" class="form-control cantidad" required></td>
                    <td><input type="number" name="productos[0][precio_unitario_ars]" step="0.01" class="form-control precio_unitario_ars" required></td>
                    <td><input type="number" step="0.01" class="form-control precio_unitario_usd" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">X</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar producto</button>

        <hr>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Total venta (ARS)</label>
                <input type="number" step="0.01" name="total_venta_ars" id="total_venta_ars" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label>Monto pagado</label>
                <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" class="form-control" value="0" required>
            </div>
            <div class="col-md-4">
                <label>Saldo pendiente</label>
                <input type="number" step="0.01" name="saldo_pendiente" id="saldo_pendiente" class="form-control" value="0" readonly>
            </div>
            <input type="hidden" name="estado_pago" id="estado_pago" value="pagado">
        </div>

        <div class="mb-3">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"></textarea>
        </div>

        <br>
        <button class="btn btn-success">Registrar venta</button>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    let fila = 1;

    function agregarFila() {
        const tbody = document.getElementById('productosBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="productos[${fila}][id]" class="form-control select-producto" required>
                    <option value="">Seleccionar...</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}"
                                data-precio-ars="{{ $producto->precio_venta_ars }}">
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
        recalcularTotal();
    }

    function eliminarFila(btn) {
        btn.closest('tr').remove();
        recalcularTotal();
    }

    function attachRowEvents(row) {
        const select = row.querySelector('.select-producto');
        const cantidad = row.querySelector('.cantidad');
        const precioArs = row.querySelector('.precio_unitario_ars');
        const precioUsd = row.querySelector('.precio_unitario_usd');

        if (select) {
            select.addEventListener('change', () => {
                const opt = select.options[select.selectedIndex];
                const precio = parseFloat(opt.dataset.precioArs || 0);
                if (precio > 0) {
                    precioArs.value = precio.toFixed(2);
                }
                actualizarPrecioUsd(row);
                recalcularTotal();
            });
        }

        if (cantidad) {
            cantidad.addEventListener('input', () => {
                recalcularTotal();
            });
        }

        if (precioArs) {
            precioArs.addEventListener('input', () => {
                actualizarPrecioUsd(row);
                recalcularTotal();
            });
        }
    }

    function actualizarPrecioUsd(row) {
        const cotizacion = parseFloat(document.getElementById('cotizacion_dolar').value) || 0;
        const precioArs = parseFloat(row.querySelector('.precio_unitario_ars').value) || 0;
        const precioUsdInput = row.querySelector('.precio_unitario_usd');
        if (cotizacion > 0 && precioArs > 0) {
            precioUsdInput.value = (precioArs / cotizacion).toFixed(2);
        } else {
            precioUsdInput.value = '';
        }
    }

    function recalcularTotal() {
        let total = 0;
        document.querySelectorAll('#productosBody tr').forEach(row => {
            const cantidad = parseFloat(row.querySelector('.cantidad')?.value) || 0;
            const precio = parseFloat(row.querySelector('.precio_unitario_ars')?.value) || 0;
            total += cantidad * precio;
        });
        const costoEnvio = parseFloat(document.getElementById('costo_envio').value) || 0;
        total += costoEnvio;
        const totalInput = document.getElementById('total_venta_ars');
        totalInput.value = total.toFixed(2);
        actualizarSaldoDesdePagado();
    }

    let actualizandoSaldo = false;

    function actualizarSaldoDesdePagado() {
        if (actualizandoSaldo) return;
        actualizandoSaldo = true;

        const total = parseFloat(document.getElementById('total_venta_ars').value) || 0;
        const pagado = parseFloat(document.getElementById('monto_pagado').value) || 0;
        const saldo = Math.max(0, total - pagado);

        const saldoInput = document.getElementById('saldo_pendiente');
        const estadoInput = document.getElementById('estado_pago');

        saldoInput.value = saldo.toFixed(2);
        estadoInput.value = saldo > 0 ? 'pendiente' : 'pagado';

        actualizandoSaldo = false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // eventos sobre filas existentes
        document.querySelectorAll('#productosBody tr').forEach(attachRowEvents);

        document.getElementById('cotizacion_dolar').addEventListener('input', () => {
            document.querySelectorAll('#productosBody tr').forEach(actualizarPrecioUsd);
        });

        document.getElementById('costo_envio').addEventListener('input', recalcularTotal);

        const pagadoInput = document.getElementById('monto_pagado');
        pagadoInput.addEventListener('input', actualizarSaldoDesdePagado);

        // vendedor -> % comision
        const vendedorSelect = document.getElementById('vendedor_id');
        const comisionInput = document.getElementById('porcentaje_comision_vendedor');

        vendedorSelect.addEventListener('change', () => {
            const opt = vendedorSelect.options[vendedorSelect.selectedIndex];
            if (vendedorSelect.value) {
                const com = parseFloat(opt.dataset.comision || 0);
                comisionInput.value = com.toFixed(2);
            } else {
                comisionInput.value = 0;
            }
        });
    });
</script>
@endsection