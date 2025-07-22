@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Detalles de la Cotización</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted">Descripción</h6>
                                    <p class="fw-bold">{{ $cotizacion->coti_descripcion }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Cliente</h6>
                                    <p class="fw-bold">{{ $cotizacion->coti_empresa }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Contacto</h6>
                                    <p class="fw-bold">{{ $cotizacion->coti_contacto }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Dirección</h6>
                                    <p class="fw-bold">{{ $cotizacion->coti_direccioncli }}, {{ $cotizacion->coti_localidad }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">CUIT</h6>
                                    <p class="fw-bold">{{ $cotizacion->coti_cuit }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted">Estado</h6>
                                    <span class="badge bg-secondary">{{ $cotizacion->coti_estado }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Fecha Alta</h6>
                                    <p class="fw-bold">{{ \Carbon\Carbon::parse($cotizacion->coti_fechaalta)->format('d/m/Y') }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Fecha Aprobación</h6>
                                    <p class="fw-bold">{{ \Carbon\Carbon::parse($cotizacion->coti_fechaaprobado)->format('d/m/Y') }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Aprobado por</h6>
                                    <p class="fw-bold">{{ trim($cotizacion->coti_aprobo) }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Responsable</h6>
                                    <p class="fw-bold">{{ trim($cotizacion->coti_responsable) }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Importe</h6>
                                    <p class="fw-bold">${{ number_format($cotizacion->coti_importe, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                @if(empty($agrupadas))
                    <div class="card-body text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-flask fa-4x text-muted mb-4"></i>
                            <h3 class="h4 text-muted mb-3">No hay muestras registradas</h3>
                            <p class="text-muted">Esta cotización no contiene muestras asociadas.</p>
                        </div>
                    </div>
                @else
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-vial me-2 text-primary"></i>
                            Muestras de la Cotización #{{ $cotizacion->coti_num }}
                        </h5>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Versión Desktop -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%" class="ps-4">Muestra</th>
                                        <th width="20%" class="text-center">Asignación</th>
                                        <th width="15%" class="text-center">Muestreo</th>
                                        <th width="15%" class="text-center">Análisis</th>
                                        <th width="10%" class="text-center">Documentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agrupadas as $grupo)
                                        <tr class="border-top border-2">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-vial-circle-check text-primary me-3 fs-5"></i>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">{{ $grupo['muestra']->cotio_descripcion }}</h6>
                                                        <small class="text-muted">Muestra #{{ $grupo['muestra']->original_item }}-{{ $grupo['muestra']->instance_number }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                @if($grupo['responsables'] && $grupo['responsables']->isNotEmpty())
                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach($grupo['responsables'] as $responsable)
                                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                                {{ $responsable->usu_descripcion }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                        <i class="fas fa-user-slash me-1"></i> Sin asignar
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="text-center">
                                                @php
                                                    $muestreoEstado = $grupo['instancia']->cotio_estado ?? 'no coordinado';
                                                    $muestreoClass = [
                                                        'muestreado' => 'success',
                                                        'en revision muestreo' => 'info',
                                                        'coordinado muestreo' => 'warning',
                                                    ][$muestreoEstado] ?? 'secondary';
                                                @endphp
                                                <span class="badge rounded-pill bg-{{ $muestreoClass }} bg-opacity-10 text-{{ $muestreoClass }}">
                                                    <i class="fas fa-{{ $muestreoEstado === 'muestreado' ? 'check-circle' : ($muestreoEstado === 'en revision muestreo' ? 'hourglass-half' : 'question-circle') }} me-1"></i>
                                                    {{ ucfirst($muestreoEstado) }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                @php
                                                    $analisisEstado = $grupo['instancia']->cotio_estado_analisis ?? 'no coordinado';
                                                    $analisisClass = [
                                                        'analizado' => 'success',
                                                        'en revision analisis' => 'info',
                                                        'coordinado analisis' => 'warning',
                                                    ][$analisisEstado] ?? 'secondary';
                                                @endphp
                                                <span class="badge rounded-pill bg-{{ $analisisClass }} bg-opacity-10 text-{{ $analisisClass }}">
                                                    <i class="fas fa-{{ $analisisEstado === 'analizado' ? 'microscope' : ($analisisEstado === 'en revision analisis' ? 'search' : 'question-circle') }} me-1"></i>
                                                    {{ ucfirst($analisisEstado) }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                @if($grupo['instancia']->enable_inform)
                                                    <a href="{{ route('informes.pdf', ['cotio_numcoti' => $grupo['instancia']->cotio_numcoti, 'cotio_item' => $grupo['instancia']->cotio_item, 'instance_number' => $grupo['instancia']->instance_number]) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-circle hover-icon"
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="Descargar informe"
                                                       style="width: 35px; height: 35px;">
                                                       <x-heroicon-o-document style="width: 20px; height: 20px;" class="text-primary document-icon"/>
                                                    </a>
                                                @else
                                                    <span class="btn btn-sm btn-outline-secondary rounded-circle disabled"
                                                          data-bs-toggle="tooltip" 
                                                          data-bs-placement="top" 
                                                          title="Informe no disponible"
                                                          style="width: 35px; height: 35px;">
                                                          <x-heroicon-o-document style="width: 20px; height: 20px;" class="text-secondary"/>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Versión Móvil -->
                        <div class="d-md-none">
                            <div class="list-group list-group-flush">
                                @foreach($agrupadas as $grupo)
                                    <div class="list-group-item border-bottom py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-vial text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $grupo['muestra']->cotio_descripcion }}</h6>
                                                    <small class="text-muted">#{{ $grupo['muestra']->original_item }}-{{ $grupo['muestra']->instance_number }}</small>
                                                </div>
                                            </div>
                                            
                                            @if($grupo['instancia']->enable_inform)
                                                <a href="{{ route('informes.pdf', ['cotio_numcoti' => $grupo['instancia']->cotio_numcoti, 'cotio_item' => $grupo['instancia']->cotio_item, 'instance_number' => $grupo['instancia']->instance_number]) }}" 
                                                    class="btn btn-sm btn-outline-primary rounded-circle hover-icon"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    title="Descargar informe"
                                                    style="width: 35px; height: 35px;">
                                                    <x-heroicon-o-document style="width: 20px; height: 20px;" class="text-primary document-icon"/>
                                                </a>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            @if($grupo['responsables'] && $grupo['responsables']->isNotEmpty())
                                                @foreach($grupo['responsables'] as $responsable)
                                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                                        <i class="fas fa-user me-1"></i>
                                                        {{ $responsable->usu_descripcion }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    <i class="fas fa-user-slash me-1"></i> Sin asignar
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted mb-1">Muestreo</small>
                                                @php
                                                    $muestreoEstado = $grupo['instancia']->cotio_estado ?? 'no coordinado';
                                                    $muestreoClass = [
                                                        'muestreado' => 'success',
                                                        'en revision muestreo' => 'info',
                                                        'no coordinado' => 'secondary'
                                                    ][$muestreoEstado] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $muestreoClass }} bg-opacity-10 text-{{ $muestreoClass }}">
                                                    {{ ucfirst($muestreoEstado) }}
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex flex-column">
                                                <small class="text-muted mb-1">Análisis</small>
                                                @php
                                                    $analisisEstado = $grupo['instancia']->cotio_estado_analisis ?? 'no coordinado';
                                                    $analisisClass = [
                                                        'analizado' => 'success',
                                                        'en revision analisis' => 'info',
                                                        'no coordinado' => 'secondary'
                                                    ][$analisisEstado] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $analisisClass }} bg-opacity-10 text-{{ $analisisClass }}">
                                                    {{ ucfirst($analisisEstado) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<style>

    .card-header.bg-primary {
        background-color: #0d6efd !important;
    }
    
    .text-muted {
        color: #6c757d !important;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    .fw-bold {
        font-weight: 600 !important;
    }
    
    .badge.bg-success {
        font-size: 0.875rem;
        padding: 0.35em 0.65em;
    }

    .empty-state {
        max-width: 400px;
        margin: 0 auto;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.08);
    }
    .list-group-item {
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Nuevos estilos para el efecto hover */
    .btn-outline-primary:hover .document-icon {
        color: white !important;
    }
    .document-icon {
        transition: color 0.2s ease-in-out;
    }
</style>
@endsection