@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Editar Método de Análisis</h2>
        <a href="{{ route('metodos-analisis.index') }}" class="btn btn-outline-secondary">
            <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" /> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('metodos-analisis.update', $metodoAnalisis) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                                           id="codigo" name="codigo" value="{{ old('codigo', $metodoAnalisis->codigo) }}" required>
                                    @error('codigo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre', $metodoAnalisis->nombre) }}" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $metodoAnalisis->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipo_requerido" class="form-label">Equipo Requerido</label>
                                    <input type="text" class="form-control @error('equipo_requerido') is-invalid @enderror" 
                                           id="equipo_requerido" name="equipo_requerido" value="{{ old('equipo_requerido', $metodoAnalisis->equipo_requerido) }}">
                                    @error('equipo_requerido')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unidad_medicion" class="form-label">Unidad de Medición</label>
                                    <input type="text" class="form-control @error('unidad_medicion') is-invalid @enderror" 
                                           id="unidad_medicion" name="unidad_medicion" value="{{ old('unidad_medicion', $metodoAnalisis->unidad_medicion) }}" 
                                           placeholder="ej: mg/L, µg/L, UFC/100ml">
                                    @error('unidad_medicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="limite_deteccion_default" class="form-label">Límite de Detección</label>
                                    <input type="number" class="form-control @error('limite_deteccion_default') is-invalid @enderror" 
                                           id="limite_deteccion_default" name="limite_deteccion_default" 
                                           value="{{ old('limite_deteccion_default', $metodoAnalisis->limite_deteccion_default) }}" step="0.000001" min="0">
                                    @error('limite_deteccion_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="limite_cuantificacion_default" class="form-label">Límite de Cuantificación</label>
                                    <input type="number" class="form-control @error('limite_cuantificacion_default') is-invalid @enderror" 
                                           id="limite_cuantificacion_default" name="limite_cuantificacion_default" 
                                           value="{{ old('limite_cuantificacion_default', $metodoAnalisis->limite_cuantificacion_default) }}" step="0.000001" min="0">
                                    @error('limite_cuantificacion_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="costo_base" class="form-label">Costo Base</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('costo_base') is-invalid @enderror" 
                                               id="costo_base" name="costo_base" value="{{ old('costo_base', $metodoAnalisis->costo_base) }}" 
                                               step="0.01" min="0">
                                        @error('costo_base')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tiempo_estimado_horas" class="form-label">Tiempo Estimado (horas)</label>
                                    <input type="number" class="form-control @error('tiempo_estimado_horas') is-invalid @enderror" 
                                           id="tiempo_estimado_horas" name="tiempo_estimado_horas" 
                                           value="{{ old('tiempo_estimado_horas', $metodoAnalisis->tiempo_estimado_horas) }}" min="1">
                                    @error('tiempo_estimado_horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="requiere_calibracion" 
                                               name="requiere_calibracion" value="1" {{ old('requiere_calibracion', $metodoAnalisis->requiere_calibracion) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requiere_calibracion">
                                            Requiere calibración
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                                               value="1" {{ old('activo', $metodoAnalisis->activo) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="activo">
                                            Método activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="procedimiento" class="form-label">Procedimiento</label>
                            <textarea class="form-control @error('procedimiento') is-invalid @enderror" 
                                      id="procedimiento" name="procedimiento" rows="4">{{ old('procedimiento', $metodoAnalisis->procedimiento) }}</textarea>
                            @error('procedimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('metodos-analisis.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Método</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
