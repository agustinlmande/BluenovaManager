@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cotización del dólar</h1>
    <hr>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('cotizacion.store') }}" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label for="valor_usd" class="form-label">Valor del dólar (ARS)</label>
                <input type="number" step="0.01" name="valor_usd" id="valor_usd" class="form-control" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </form>

    <h5>Historial de cotizaciones</h5>
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Valor (ARS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cotizaciones as $c)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y H:i') }}</td>
                    <td>${{ number_format($c->valor_usd, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2">No hay cotizaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
