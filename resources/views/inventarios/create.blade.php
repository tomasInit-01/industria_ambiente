@extends('layouts.app')

@section('content')
    <h1>Crear Inventario</h1>
    <form action="{{ route('inventarios.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="equipamiento" class="form-label">Equipamiento</label>
            <input type="text" class="form-control" id="equipamiento" name="equipamiento" required placeholder="Ej: Camara de muestreo">
        </div>
        <div class="mb-3">
            <label for="marca_modelo" class="form-label">Marca y Modelo</label>
            <input type="text" class="form-control" id="marca_modelo" name="marca_modelo" required placeholder="Ej: ABC123">
        </div>
        <div class="mb-3">
            <label for="n_serie_lote" class="form-label">N° de Serie o Lote</label>
            <input type="text" class="form-control" id="n_serie_lote" name="n_serie_lote" required placeholder="Ej: ABC123">
        </div>
        <div class="mb-3">
            <label for="activo" class="form-label">Activo</label>
            <select class="form-select" id="activo" name="activo">
                <option value="true">Activo</option>
                <option value="false">Inactivo</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="fecha_calibracion" class="form-label">Fecha de Calibración</label>
            <input type="date" class="form-control" id="fecha_calibracion" name="fecha_calibracion" required placeholder="Ej: 2022-01-01">
        </div>
        <div class="mb-3">
            <label for="codigo_ficha" class="form-label">Código de Ficha</label>
            <input type="text" class="form-control" id="codigo_ficha" name="codigo_ficha" required placeholder="Ej: ABC123">
        </div>

        {{-- añadir input path certificado de calibracion pdf --}}
        <div class="mb-3">
            <label for="certificado_calibracion" class="form-label">Certificado de Calibración</label>
            <input type="file" class="form-control" id="certificado_calibracion" name="certificado_calibracion" accept=".pdf">
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

@endsection