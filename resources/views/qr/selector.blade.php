@extends('layouts.app')

<head>
    <title>Selector de Vista - {{ $instancia->muestra->cotizacion->coti_num ?? $cotio_numcoti }}</title>
</head>

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Selector de Vista
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h5 class="text-muted">Cotización: <strong class="text-primary">{{ $cotio_numcoti }}</strong></h5>
                        <p class="text-muted">Muestra: <strong>{{ $instance }}</strong></p>
                        @if($instancia && $instancia->muestra && $instancia->muestra->cotizacion)
                            <p class="text-muted">{{ $instancia->muestra->cotizacion->coti_descripcion ?? '' }}</p>
                        @endif
                    </div>

                    <div class="row g-3">
                        <!-- Opción Muestreo -->
                        <div class="col-12">
                            <a href="{{ route('tareas.all.show', [
                                'cotio_numcoti' => $cotio_numcoti,
                                'cotio_item' => $cotio_item,
                                'cotio_subitem' => $cotio_subitem,
                                'instance' => $instance
                            ]) }}" class="btn btn-outline-success btn-lg w-100 py-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-clipboard-list me-3 fs-4"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Vista de Muestreo</div>
                                    <small class="text-muted">Trabajo de campo y recolección</small>
                                </div>
                            </a>
                        </div>

                        <!-- Opción Laboratorio -->
                        <div class="col-12">
                            <a href="{{ route('ordenes.all.show', [
                                'cotio_numcoti' => $cotio_numcoti,
                                'cotio_item' => $cotio_item,
                                'cotio_subitem' => $cotio_subitem,
                                'instance' => $instance
                            ]) }}" class="btn btn-outline-primary btn-lg w-100 py-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-flask me-3 fs-4"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Vista de Laboratorio</div>
                                    <small class="text-muted">Análisis y resultados</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Como administrador, puedes acceder a ambas vistas
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.btn-lg {
    border-radius: 10px;
    border-width: 2px;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endsection
