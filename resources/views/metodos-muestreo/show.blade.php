@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Método de Muestreo: {{ $metodoMuestreo->nombre }}</h2>
        <div>
            <a href="{{ route('metodos-muestreo.edit', $metodoMuestreo) }}" class="btn btn-primary me-2">
                <x-heroicon-o-pencil style="width: 16px; height: 16px;" class="me-1" /> Editar
            </a>
            <a href="{{ route('metodos-muestreo.index') }}" class="btn btn-outline-secondary">
                <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" /> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información del Método</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Código:</label>
                                <p><code>{{ $metodoMuestreo->codigo }}</code></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p>
                                    @if($metodoMuestreo->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre:</label>
                        <p>{{ $metodoMuestreo->nombre }}</p>
                    </div>

                    @if($metodoMuestreo->descripcion)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción:</label>
                            <p>{{ $metodoMuestreo->descripcion }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            @if($metodoMuestreo->equipo_requerido)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Equipo Requerido:</label>
                                    <p>{{ $metodoMuestreo->equipo_requerido }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($metodoMuestreo->unidad_medicion)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Unidad de Medición:</label>
                                    <p>{{ $metodoMuestreo->unidad_medicion }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($metodoMuestreo->procedimiento)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Procedimiento:</label>
                            <div class="border rounded p-3 bg-light">
                                <pre class="mb-0">{{ $metodoMuestreo->procedimiento }}</pre>
                            </div>
                        </div>
                    @endif

                    @if($metodoMuestreo->costo_base)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Costo Base:</label>
                            <p class="text-success fw-bold">${{ number_format($metodoMuestreo->costo_base, 2) }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Creado:</label>
                                <p>{{ $metodoMuestreo->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Última actualización:</label>
                                <p>{{ $metodoMuestreo->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Uso del Método</h5>
                </div>
                <div class="card-body">
                    @if($metodoMuestreo->cotios->count() > 0)
                        <p class="text-success">
                            <i class="fas fa-check-circle"></i>
                            Este método está siendo usado en <strong>{{ $metodoMuestreo->cotios->count() }}</strong> cotización(es).
                        </p>
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle"></i>
                                No se puede eliminar este método mientras esté en uso.
                            </small>
                        </div>
                    @else
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Este método no está siendo usado actualmente.
                        </p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('metodos-muestreo.edit', $metodoMuestreo) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Método
                        </a>
                        <a href="{{ route('metodos-muestreo.delete', $metodoMuestreo) }}" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Eliminar Método
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
