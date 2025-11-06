<div class="row mb-3">
    <div class="col-md-6">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $vendedor->nombre ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Contacto</label>
        <input type="text" name="contacto" class="form-control" value="{{ old('contacto', $vendedor->contacto ?? '') }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label>Comisión por defecto (%)</label>
        <input type="number" name="comision_por_defecto" step="0.01" class="form-control"
            value="{{ old('comision_por_defecto', $vendedor->comision_por_defecto ?? 0) }}" required>
    </div>
    <div class="col-md-8">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" value="{{ old('observaciones', $vendedor->observaciones ?? '') }}">
    </div>
</div>