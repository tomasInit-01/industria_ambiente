@extends('layouts.app')
<head>
    <title>Editar Vehículo</title>
</head>

@section('content')
<div class="container">
    <h1>Editar Vehículo</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ups!</strong> Hay algunos errores:<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vehiculos.update', $vehiculo->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca', $vehiculo->marca) }}">
        </div>

        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" class="form-control" id="modelo" name="modelo" value="{{ old('modelo', $vehiculo->modelo) }}">
        </div>

        <div class="mb-3">
            <label for="anio" class="form-label">Año</label>
            <input type="number" class="form-control" id="anio" name="anio" value="{{ old('anio', $vehiculo->anio) }}">
        </div>

        <div class="mb-3">
            <label for="patente" class="form-label">Patente <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="patente" name="patente" value="{{ old('patente', $vehiculo->patente) }}" required>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <input type="text" class="form-control" id="tipo" name="tipo" value="{{ old('tipo', $vehiculo->tipo) }}">
        </div>

        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="libre" {{ old('estado', $vehiculo->estado) === 'libre' ? 'selected' : '' }}>Libre</option>
                <option value="ocupado" {{ old('estado', $vehiculo->estado) === 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                <option value="mantenimiento" {{ old('estado', $vehiculo->estado) === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="desconocido" {{ old('estado', $vehiculo->estado) === 'desconocido' ? 'selected' : '' }}>Desconocido</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="ultimo_mantenimiento" class="form-label">Ultimo mantenimiento</label>
            <input type="date" class="form-control" id="ultimo_mantenimiento" name="ultimo_mantenimiento" value="{{ old('ultimo_mantenimiento', $vehiculo->ultimo_mantenimiento) }}">
        </div>

        <div class="mb-3">
            <label for="estado_gral" class="form-label">Estado general</label>
            <input type="text" class="form-control" id="estado_gral" name="estado_gral" value="{{ old('estado_gral', $vehiculo->estado_gral) }}">
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $vehiculo->descripcion) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar Cambios</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
