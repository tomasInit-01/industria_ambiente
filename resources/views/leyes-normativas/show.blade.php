@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $leyNormativa->nombre }}</h2>
        <div>
            <a href="{{ route('leyes-normativas.edit', $leyNormativa) }}" class="btn btn-primary me-2">
                <x-heroicon-o-pencil style="width: 16px; height: 16px;" class="me-1" /> Editar
            </a>
            <a href="{{ route('leyes-normativas.index') }}" class="btn btn-outline-secondary">
                <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" /> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información de la Normativa</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Código:</label>
                                <p><code>{{ $leyNormativa->codigo }}</code></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Grupo:</label>
                                <p><span class="badge bg-info">{{ $leyNormativa->grupo }}</span></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p>
                                    @if($leyNormativa->activo)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($leyNormativa->articulo)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Artículo:</label>
                            <p>{{ $leyNormativa->articulo }}</p>
                        </div>
                    @endif

                    @if($leyNormativa->descripcion)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción:</label>
                            <p>{{ $leyNormativa->descripcion }}</p>
                        </div>
                    @endif

                    @if($leyNormativa->variables->count() > 0)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Variables Asociadas:</label>
                            <div class="row">
                                @foreach($leyNormativa->variables as $variable)
                                    <div class="col-md-12 mb-3">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            @if($variable->cotioItem)
                                                                <code>{{ $variable->cotioItem->id }}</code> - {{ $variable->cotioItem->cotio_descripcion }}
                                                            @else
                                                                {{ $variable->nombre }}
                                                            @endif
                                                        </h6>
                                                        <div class="mt-2">
                                                            @if($variable->cotioItem)
                                                                @if($variable->cotioItem->matriz)
                                                                    <span class="badge bg-primary me-1">
                                                                        <i class="fas fa-flask"></i> Matriz: {{ $variable->cotioItem->matriz->matriz_descripcion }}
                                                                    </span>
                                                                @endif
                                                                @php
                                                                    $metodos = [];
                                                                    if($variable->cotioItem->metodoAnalitico) {
                                                                        $metodos[] = $variable->cotioItem->metodoAnalitico->metodo_descripcion;
                                                                    }
                                                                    if($variable->cotioItem->metodoMuestreo) {
                                                                        $metodos[] = $variable->cotioItem->metodoMuestreo->metodo_descripcion;
                                                                    }
                                                                @endphp
                                                                @if(!empty($metodos))
                                                                    <span class="badge bg-info me-1">
                                                                        <i class="fas fa-cogs"></i> Métodos: {{ implode(' / ', $metodos) }}
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <small class="text-muted">
                                                                    <code>{{ $variable->codigo }}</code>
                                                                    @if($variable->tipo_variable)
                                                                        • {{ $variable->tipo_variable }}
                                                                    @endif
                                                                </small>
                                                            @endif
                                                        </div>
                                                        @if($variable->pivot->valor_limite)
                                                            <div class="mt-2">
                                                                <small class="text-info">
                                                                    <strong><i class="fas fa-chart-line"></i> Valor Límite:</strong> 
                                                                    {{ $variable->pivot->valor_limite }}
                                                                    @if($variable->pivot->unidad_medida)
                                                                        <span class="badge bg-secondary">{{ $variable->pivot->unidad_medida }}</span>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($leyNormativa->variables_aplicables)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Variables Aplicables (Texto):</label>
                            <p>{{ $leyNormativa->variables_aplicables }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            @if($leyNormativa->organismo_emisor)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Organismo Emisor:</label>
                                    <p>{{ $leyNormativa->organismo_emisor }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($leyNormativa->fecha_vigencia)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fecha de Vigencia:</label>
                                    <p>{{ $leyNormativa->fecha_vigencia->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($leyNormativa->fecha_actualizacion)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Última Actualización:</label>
                            <p>{{ $leyNormativa->fecha_actualizacion->format('d/m/Y') }}</p>
                        </div>
                    @endif

                    @if($leyNormativa->observaciones)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observaciones:</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">{{ $leyNormativa->observaciones }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Uso de la Normativa</h5>
                </div>
                <div class="card-body">
                    @if($leyNormativa->cotios->count() > 0)
                        <p class="text-success">
                            <i class="fas fa-check-circle"></i>
                            Esta normativa está siendo usada en <strong>{{ $leyNormativa->cotios->count() }}</strong> cotización(es).
                        </p>
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle"></i>
                                No se puede eliminar esta normativa mientras esté en uso.
                            </small>
                        </div>
                    @else
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Esta normativa no está siendo usada actualmente.
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
                        <a href="{{ route('leyes-normativas.edit', $leyNormativa) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Normativa
                        </a>
                        <a href="{{ route('leyes-normativas.delete', $leyNormativa) }}" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Eliminar Normativa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
