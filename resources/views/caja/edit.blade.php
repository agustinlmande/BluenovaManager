@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Editar Movimiento</h1>

    <form action="{{ route('caja.update', $caja->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tipo de movimiento</label>
            <select name="tipo" class="form-select" required>
                <option value="ingreso" {{ $caja->tipo == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                <option value="egreso" {{ $caja->tipo == 'egreso' ? 'selected' : '' }}>Egreso</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" name="monto" class="form-control" step="0.01" min="0"
                   value="{{ $caja->monto }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control"
                   value="{{ $caja->motivo }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="fecha" class="form-control"
                   value="{{ \Carbon\Carbon::parse($caja->fecha)->format('Y-m-d\TH:i') }}"
                   max="{{ now()->format('Y-m-d\TH:i') }}" required>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('caja.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
