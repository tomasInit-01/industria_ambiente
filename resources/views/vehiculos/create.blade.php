@extends('layouts.app')

@section('content')
    <h1>Crear Vehiculo</h1>
    <form action="{{ route('vehiculos.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" class="form-control" id="marca" name="marca" required placeholder="Ej: Toyota">
        </div>
        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" class="form-control" id="modelo" name="modelo" required placeholder="Ej: Camry">
        </div>
        <div class="mb-3">
            <label for="anio" class="form-label">Año</label>
            <input type="text" class="form-control" id="anio" name="anio" placeholder="Ej: 2022">
        </div>
        <div class="mb-3">
            <label for="patente" class="form-label">Patente</label>
            <input type="text" class="form-control" id="patente" name="patente" required placeholder="Ej: ABC123">
        </div>
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <input type="text" class="form-control" id="tipo" name="tipo" placeholder="Ej: Automovil">
        </div>
        <div class="mb-3">
            <label for="ultimo_mantenimiento" class="form-label">Ultimo mantenimiento</label>
            <input type="date" class="form-control" id="ultimo_mantenimiento" name="ultimo_mantenimiento">
        </div>
        <div class="mb-3">
            <label for="estado_gral" class="form-label">Estado general</label>
            <input type="text" class="form-control" id="estado_gral" name="estado_gral" placeholder="Ej: óptimas condiciones">
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Ej: Auto para pruebas"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

@endsection