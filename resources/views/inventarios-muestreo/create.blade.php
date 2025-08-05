@extends('layouts.app')

@section('content')
    <h1>Crear Inventario de Muestreo</h1>
    <form action="{{ route('inventarios-muestreo.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="equipamiento" class="form-label">Equipamiento</label>
            <input type="text" class="form-control" id="equipamiento" name="equipamiento" required placeholder="Ej: Camara de muestreo">
        </div>
        <div class="mb-3">
            <label for="marca_modelo" class="form-label">Marca y Modelo</label>
            <input type="text" class="form-control" id="marca_modelo" name="marca_modelo" placeholder="Ej: ABC123">
        </div>
        <div class="mb-3">
            <label for="n_serie_lote" class="form-label">N° de Serie o Lote</label>
            <input type="text" class="form-control" id="n_serie_lote" name="n_serie_lote" placeholder="Ej: ABC123">
        </div>
        <div class="mb-3">
            <label for="fecha_calibracion" class="form-label">Fecha de Calibración (vencimiento)</label>
            <input type="date" class="form-control" id="fecha_calibracion" name="fecha_calibracion" placeholder="Fecha de calibración">
        </div>
        <div class="mb-3">
            <label for="activo" class="form-label">Activo</label>
            <select class="form-select" id="activo" name="activo">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="certificado" class="form-label">Certificado de Calibración (PDF)</label>
            <input type="file" class="form-control" id="certificado" name="certificado" accept=".pdf">
            <small class="text-muted">Tamaño máximo: 5MB</small>
        </div>
        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
@endsection