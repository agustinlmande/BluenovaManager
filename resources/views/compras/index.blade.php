@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Compras registradas</h1>

    <a href="{{ route('compras.create') }}" class="btn btn-primary mb-3">Nueva compra</a>

    {{-- 🔍 Buscador + Botones de filtro --}}
    <div class="d-flex align-items-center mb-3">
        <form action="{{ route('compras.index') }}" method="GET" class="w-100 d-flex align-items-center gap-2">

            {{-- Campo de búsqueda --}}
            <input type="text" name="buscar" class="form-control w-100"
                placeholder="Buscar compra (proveedor, fecha o total...)"
                value="{{ request('buscar') }}">

            {{-- Botón buscar --}}
            <button type="submit" class="btn btn-outline-primary d-flex align-items-center gap-1">
                <i class="bi bi-search"></i> <span>Buscar</span>
            </button>

            {{-- Botones de filtros --}} <!--
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFecha">
                <i class="bi bi-funnel"></i> Fecha
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalProveedor">
                <i class="bi bi-funnel"></i> Proveedor
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUSD">
                <i class="bi bi-funnel"></i> USD
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalARS">
                <i class="bi bi-funnel"></i> ARS
            </button> -->
        </form>
    </div>

    {{-- 🏷️ Filtros activos --}}
    @if(request()->except(['page', '_token']) && collect(request()->except(['page', '_token']))->filter()->isNotEmpty())
    <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
        {{-- 🔍 Búsqueda --}}
        @if(request('buscar'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">Búsqueda:</strong> "{{ request('buscar') }}"
            <a href="{{ route('compras.index', collect(request()->except('buscar'))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        {{-- 📅 Fecha --}}
        @if(request('fecha_desde') || request('fecha_hasta'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">Fecha:</strong>
            {{ request('fecha_desde') ? 'Desde ' . request('fecha_desde') : '' }}
            {{ request('fecha_hasta') ? ' Hasta ' . request('fecha_hasta') : '' }}
            <a href="{{ route('compras.index', collect(request()->except(['fecha_desde','fecha_hasta']))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        {{-- 🏢 Proveedor --}}
        @if(request('proveedor'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">Proveedor:</strong> {{ request('proveedor') }}
            <a href="{{ route('compras.index', collect(request()->except('proveedor'))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        {{-- 💵 USD --}}
        @if(request('usd_filtro'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">USD:</strong>
            @if(request('usd_filtro') === 'entre')
                Entre {{ request('usd_desde') }} y {{ request('usd_hasta') }}
            @else
                {{ ucfirst(request('usd_filtro')) }} {{ request('usd_valor') }}
            @endif
            <a href="{{ route('compras.index', collect(request()->except(['usd_filtro','usd_valor','usd_desde','usd_hasta']))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        {{-- 💰 ARS --}}
        @if(request('ars_filtro'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">ARS:</strong>
            @if(request('ars_filtro') === 'entre')
                Entre {{ request('ars_desde') }} y {{ request('ars_hasta') }}
            @else
                {{ ucfirst(request('ars_filtro')) }} {{ request('ars_valor') }}
            @endif
            <a href="{{ route('compras.index', collect(request()->except(['ars_filtro','ars_valor','ars_desde','ars_hasta']))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        <a href="{{ route('compras.index') }}" class="badge bg-danger text-white text-decoration-none ms-2">
            Limpiar todo ✕
        </a>
    </div>
    @endif

    {{-- 📋 Tabla --}}
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th class="text-center align-middle">
                     <div class="d-flex align-items-center gap-2 ">
                       <span class="fw-semibold mb-0">Fecha</span> 
                       <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalFecha">
                <i class="bi bi-funnel"></i> Filtrar
            </button> </div> </th>
                 <th class="text-center align-middle">
                     <div class="d-flex align-items-center gap-2 ">
                       <span class="fw-semibold mb-0">Proveedor</span>  <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalProveedor">
                <i class="bi bi-funnel"></i> Filtrar
            </button> </div></th>
                <th class="text-center align-middle">
                     <div class="d-flex align-items-center gap-2 ">
                       <span class="fw-semibold mb-0">Total (USD)</span> <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalUSD">
                <i class="bi bi-funnel"></i> Filtrar
            </button> </div></th>
                <th class="text-center align-middle">
                     <div class="d-flex align-items-center gap-2 ">
                       <span class="fw-semibold mb-0">Total (ARS)</span> <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalARS">
                <i class="bi bi-funnel"></i> Filtrar
            </button> </div></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $compra)
            <tr>
                <td>{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                <td>{{ $compra->proveedor ?? '-' }}</td>
                <td>U$D {{ number_format($compra->total_usd, 2, ',', '.') }}</td>
                <td>$ {{ number_format($compra->total_ars, 2, ',', '.') }}</td>
                <td>
                    <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detalle{{ $compra->id }}">Ver detalles</button>
                    <a href="{{ route('compras.edit', $compra) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('compras.destroy', $compra) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar compra?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <tr class="collapse" id="detalle{{ $compra->id }}">
                <td colspan="5">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio USD</th>
                                <th>Subtotal USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>{{ number_format($detalle->precio_unitario_usd, 2, ',', '.') }}</td>
                                <td>{{ number_format($detalle->precio_unitario_usd * $detalle->cantidad, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No se encontraron resultados</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- 🗓 Modal Fecha --}}
<div class="modal fade" id="modalFecha" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Filtrar por fecha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ route('compras.index') }}">
                    @foreach(request()->except(['page','_token','fecha_desde','fecha_hasta']) as $k=>$v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <div class="mb-3">
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="mb-3">
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aplicar filtro</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 🏢 Modal Proveedor --}}
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Filtrar por proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ route('compras.index') }}">
                    @foreach(request()->except(['page','_token','proveedor']) as $k=>$v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <input type="text" name="proveedor" class="form-control mb-3"
                        placeholder="Nombre del proveedor..." value="{{ request('proveedor') }}">
                    <button type="submit" class="btn btn-primary w-100">Aplicar filtro</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 💵 Modal USD --}}
<div class="modal fade" id="modalUSD" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Filtrar por Total USD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="GET" action="{{ route('compras.index') }}">
                    @foreach(request()->except(['page','_token','usd_filtro','usd_valor','usd_desde','usd_hasta']) as $k=>$v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="usd_filtro" class="form-select mb-3" id="usdFiltro" onchange="mostrarCamposUSD()">
                        <option value="">Seleccionar...</option>
                        <option value="mayor">Mayor que</option>
                        <option value="menor">Menor que</option>
                        <option value="entre">Entre</option>
                    </select>
                    <div id="usdCampos"></div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Aplicar filtro</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 💰 Modal ARS --}}
<div class="modal fade" id="modalARS" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Filtrar por Total ARS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="GET" action="{{ route('compras.index') }}">
                    @foreach(request()->except(['page','_token','ars_filtro','ars_valor','ars_desde','ars_hasta']) as $k=>$v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="ars_filtro" class="form-select mb-3" id="arsFiltro" onchange="mostrarCamposARS()">
                        <option value="">Seleccionar...</option>
                        <option value="mayor">Mayor que</option>
                        <option value="menor">Menor que</option>
                        <option value="entre">Entre</option>
                    </select>
                    <div id="arsCampos"></div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Aplicar filtro</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarCamposUSD() {
    const f = document.getElementById('usdFiltro').value;
    const c = document.getElementById('usdCampos');
    c.innerHTML = '';
    if (f === 'mayor' || f === 'menor') {
        c.innerHTML = `<input type="number" step="0.01" name="usd_valor" class="form-control" placeholder="Valor">`;
    } else if (f === 'entre') {
        c.innerHTML = `<div class="d-flex gap-2">
            <input type="number" step="0.01" name="usd_desde" class="form-control" placeholder="Desde">
            <input type="number" step="0.01" name="usd_hasta" class="form-control" placeholder="Hasta">
        </div>`;
    }
}
function mostrarCamposARS() {
    const f = document.getElementById('arsFiltro').value;
    const c = document.getElementById('arsCampos');
    c.innerHTML = '';
    if (f === 'mayor' || f === 'menor') {
        c.innerHTML = `<input type="number" step="0.01" name="ars_valor" class="form-control" placeholder="Valor">`;
    } else if (f === 'entre') {
        c.innerHTML = `<div class="d-flex gap-2">
            <input type="number" step="0.01" name="ars_desde" class="form-control" placeholder="Desde">
            <input type="number" step="0.01" name="ars_hasta" class="form-control" placeholder="Hasta">
        </div>`;
    }
}
</script>
@endsection
