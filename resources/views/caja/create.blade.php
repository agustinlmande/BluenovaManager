@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Nuevo Movimiento</h1>

    <form action="{{ route('caja.store') }}" method="POST" class="mt-4">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tipo de movimiento</label>
            <select name="tipo" class="form-select" required>
                <option value="">Seleccionar...</option>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" name="monto" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control"
                   placeholder="Ej: Venta directa, gasto de envío..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="fecha" class="form-control"
                   max="{{ now()->format('Y-m-d\TH:i') }}" required>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('caja.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
