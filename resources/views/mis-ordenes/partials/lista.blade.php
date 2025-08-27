<?php
// Ordenar los grupos por estado y fecha
$gruposRevisionResultados = []; // New group for orders needing result review
$gruposPrioritarios = [];
$gruposCoordinados = [];
$gruposEnRevision = [];
$gruposFinalizados = [];

foreach ($ordenesAgrupadas as $key => $grupo) {
    $isLista = $viewType === 'lista';
    
    // Determinar el estado del grupo y revisar request_review
    $estadosMuestras = [];
    $hasPriority = false;
    $needsResultReview = false; // Flag for request_review

    if ($isLista) {
        foreach ($grupo['instancias'] as $instancia) {
            $estadosMuestras[] = strtolower($instancia['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
            if ($instancia['is_priority'] ?? false) {
                $hasPriority = true;
            }
            // Check if any analysis in this instance has request_review = true
            foreach ($instancia['analisis'] as $analisis) {
                if ($analisis->request_review ?? false) {
                    $needsResultReview = true;
                    break; // No need to check further analyses in this instance
                }
            }
            if ($needsResultReview) {
                break; // No need to check further instances
            }
        }
    } else {
        $estadosMuestras[] = strtolower($grupo['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
        if ($grupo['is_priority'] ?? false) {
            $hasPriority = true;
        }
        // Check if any analysis in this group has request_review = true
        foreach ($grupo['analisis'] as $analisis) {
            if ($analisis->request_review ?? false) {
                $needsResultReview = true;
                break;
            }
        }
    }
    
    // Determinar el estado del grupo según la jerarquía
    $estadoGrupo = 'pendiente';
    if ($needsResultReview) {
        $estadoGrupo = 'revisión de resultados'; // Override other states
    } elseif (in_array('coordinado', $estadosMuestras) || in_array('coordinado analisis', $estadosMuestras)) {
        $estadoGrupo = 'coordinado';
    } elseif (in_array('en revision analisis', $estadosMuestras)) {
        if (!in_array('coordinado', $estadosMuestras) && !in_array('coordinado analisis', $estadosMuestras)) {
            $estadoGrupo = 'en revision analisis';
        }
    } elseif (count(array_unique($estadosMuestras)) === 1 && in_array('analizado', $estadosMuestras)) {
        $estadoGrupo = 'analizado';
    }
    
    // Obtener fecha de muestreo
    $fechaMuestreo = $isLista 
        ? ($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot ?? null)
        : ($grupo['instancia_muestra']->fecha_inicio_ot ?? null);
    
    $grupoConFecha = [
        'grupo' => $grupo,
        'key' => $key,
        'fecha' => $fechaMuestreo ? \Carbon\Carbon::parse($fechaMuestreo) : null,
        'isLista' => $isLista,
        'hasPriority' => $hasPriority,
        'estadoGrupo' => $estadoGrupo
    ];
    
    // Clasificar el grupo
    if ($needsResultReview) {
        $gruposRevisionResultados[] = $grupoConFecha;
    } elseif ($hasPriority && $estadoGrupo !== 'analizado') {
        // Solo mostrar en prioritarios si NO está analizado
        $gruposPrioritarios[] = $grupoConFecha;
    } elseif ($estadoGrupo === 'coordinado') {
        $gruposCoordinados[] = $grupoConFecha;
    } elseif ($estadoGrupo === 'en revision analisis') {
        $gruposEnRevision[] = $grupoConFecha;
    } elseif ($estadoGrupo === 'analizado') {
        $gruposFinalizados[] = $grupoConFecha;
    }
}

// Funciones para ordenar por fecha (más recientes primero)
$sortFunction = function($a, $b) {
    if ($a['fecha'] && $b['fecha']) {
        return $b['fecha'] <=> $a['fecha'];
    }
    return 0;
};

usort($gruposRevisionResultados, $sortFunction);
usort($gruposPrioritarios, $sortFunction);
usort($gruposCoordinados, $sortFunction);
usort($gruposEnRevision, $sortFunction);
usort($gruposFinalizados, $sortFunction);
?>

<style>
    .priority-group {
        border-left: 4px solid #ffc107;
        background-color: rgba(255, 193, 7, 0.1);
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.2);
    }
    
    .priority-instance {
        position: relative;
        padding-left: 30px !important;
    }
    
    .priority-badge {
        background-color: #ffc107;
        color: #000;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Mantener los colores por estado pero con prioridad */
    .table-warning.priority-instance {
        background-color: rgba(255, 243, 205, 0.9) !important;
    }
    .table-info.priority-instance {
        background-color: rgba(209, 236, 241, 0.9) !important;
    }
    .table-success.priority-instance {
        background-color: rgba(212, 237, 218, 0.9) !important;
    }
    
    .priority-highlight {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }
</style>

@if(count($ordenesAgrupadas) > 0)
    <!-- Sección de Órdenes Coordinadas -->

    @if(count($gruposRevisionResultados) > 0)
        <div style="margin-bottom: 40px;">
            <h3 class="text-primary mb-3">
                <x-heroicon-o-exclamation-circle class="me-2" style="width: 24px; height: 24px;" />
                Revisión de Resultados ({{ count($gruposRevisionResultados) }})
            </h3>
            
            @foreach($gruposRevisionResultados as $grupoData)
                @php
                    $grupo = $grupoData['grupo'];
                    $key = $grupoData['key'];
                    $isLista = $grupoData['isLista'];
                    $hasPriority = $grupoData['hasPriority'];
                    $estadoGrupo = $grupoData['estadoGrupo'];
                    $cotizacion = $cotizaciones->get($isLista ? $key : explode('_', $key)[0]);
                    
                    $badgeClassMuestra = 'primary'; // Use primary for Revisión de Resultados
                @endphp

                <div class="card mb-4 shadow-sm revision-resultados-group">
                    <div class="card-header table-primary">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none p-0 me-2" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" 
                                        aria-expanded="false" 
                                        aria-controls="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}"
                                        onclick="toggleChevron('chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}')">
                                    <x-heroicon-o-chevron-up id="chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                                </button>
                                <div>
                                    <h4 class="mb-0 text-primary">
                                        Cotización Nº {{ $isLista ? $key : explode('_', $key)[0] }} 
                                        @if($isLista)
                                            - ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-primary text-white">
                                            Revisión de Resultados
                                        </span>
                                        @if($hasPriority)
                                            <span class="badge priority-badge">
                                                <x-heroicon-o-star class="me-1" style="width: 14px; height: 14px;" />
                                                Prioridad
                                            </span>
                                        @endif
                                        @if($isLista && $grupo['instancias'][0]['instancia_muestra']->es_frecuente && $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @elseif(!$isLista && $grupo['instancia_muestra']->es_frecuente && $grupo['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a class="btn btn-outline-primary btn-sm"
                                   href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli ?? '' }}, {{ $cotizacion->coti_localidad ?? '' }}, {{ $cotizacion->coti_partido ?? '' }}">
                                    <x-heroicon-o-map class="me-1" style="width: 16px; height: 16px;" />
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>

                        @if($cotizacion)
                            <div class="mt-3 small">
                                <div class="row g-2">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Fecha: </strong> 
                                        @if($isLista)
                                            {{ \Carbon\Carbon::parse($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @else
                                            {{ \Carbon\Carbon::parse($grupo['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="w-60">Descripción</th>
                                            <th class="w-40">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isLista)
                                            @foreach($grupo['instancias'] as $instancia)
                                                @php
                                                    $muestra = $instancia['muestra'];
                                                    $instanciaMuestra = $instancia['instancia_muestra'];
                                                    $analisis = $instancia['analisis'];
                                                    $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                    $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                    $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                    $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassMuestra = match ($estadoMuestra) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $isPriority = $instancia['is_priority'] ?? false;
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if($isPriority)
                                                                    <span class="badge priority-badge me-2">
                                                                        <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                        Prioritaria
                                                                    </span>
                                                                @endif
                                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} (#{{ $instanciaMuestra->instance_number }})</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                                @if($esFrecuente && $frecuenciaDias > 0)
                                                                    <span class="badge bg-light text-dark border mt-1">
                                                                        <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                        Cada {{ $frecuenciaDias }} días
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                               class="btn btn-sm btn-dark">
                                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                            {{ ucfirst($estadoMuestra) }}
                                                        </span>
                                                    </td>
                                                </tr>

                                                                                                 @foreach($analisis as $tarea)
                                                    @php
                                                        $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                        $badgeClassAnalisis = match ($estado) {
                                                            'coordinado', 'coordinado analisis' => 'table-warning',
                                                            'en revision analisis' => 'table-info',
                                                            'analizado' => 'table-success',
                                                            'suspension' => 'table-danger text-white',
                                                            default => 'table-secondary'
                                                        };
                                                        $needsReview = $tarea->request_review ?? false;
                                                    @endphp
                                                    <tr class="{{ $badgeClassAnalisis }}">
                                                        <td class="small">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                    <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                    @if($tarea->resultado)
                                                                        <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                    @endif
                                                                    @if($needsReview)
                                                                        <span class="badge revision-resultados-badge mt-1">
                                                                            <x-heroicon-o-exclamation-circle class="me-1" style="width: 14px; height: 14px;" />
                                                                            Revisión Requerida
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                                {{ ucfirst($estado) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $muestra = $grupo['muestra'];
                                                $instanciaMuestra = $grupo['instancia_muestra'];
                                                $analisis = $grupo['analisis'];
                                                $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassMuestra = match ($estadoMuestra) {
                                                    'coordinado', 'coordinado analisis' => 'table-warning',
                                                    'en revision analisis' => 'table-info',
                                                    'analizado' => 'table-success',
                                                    'suspension' => 'table-danger text-white',
                                                    default => 'table-secondary'
                                                };
                                                $isPriority = $grupo['is_priority'] ?? false;
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @if($isPriority)
                                                                <span class="badge priority-badge me-2">
                                                                    <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                    Prioritaria
                                                                </span>
                                                            @endif
                                                            <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                            <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                            @if($esFrecuente && $frecuenciaDias > 0)
                                                                <span class="badge bg-light text-dark border mt-1">
                                                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                    Cada {{ $frecuenciaDias }} días
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                           class="btn btn-sm btn-dark">
                                                            <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                            Ver
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                        {{ ucfirst($estadoMuestra) }}
                                                    </span>
                                                </td>
                                            </tr>

                                                                                             @foreach($analisis as $tarea)
                                                @php
                                                    $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassAnalisis = match ($estado) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $needsReview = $tarea->request_review ?? false;
                                                @endphp
                                                <tr class="{{ $badgeClassAnalisis }}">
                                                    <td class="small">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                @if($tarea->resultado)
                                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                @endif
                                                                @if($needsReview)
                                                                    <span class="badge revision-resultados-badge mt-1">
                                                                        <x-heroicon-o-exclamation-circle class="me-1" style="width: 14px; height: 14px;" />
                                                                        Revisión Requerida
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                            {{ ucfirst($estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif


    @if(count($gruposPrioritarios) > 0)
        <div style="margin-bottom: 40px;">
            <h3 class="text-warning mb-3">
                <x-heroicon-o-star class="me-2" style="width: 24px; height: 24px;" />
                Órdenes Prioritarias ({{ count($gruposPrioritarios) }})
            </h3>
            
            @foreach($gruposPrioritarios as $grupoData)
                @php
                    $grupo = $grupoData['grupo'];
                    $key = $grupoData['key'];
                    $isLista = $grupoData['isLista'];
                    $hasPriority = $grupoData['hasPriority'];
                    $estadoGrupo = $grupoData['estadoGrupo'];
                    $cotizacion = $cotizaciones->get($isLista ? $key : explode('_', $key)[0]);
                    
                    $badgeClassMuestra = match ($estadoGrupo) {
                        'coordinado' => 'warning',
                        'en revision analisis' => 'info',
                        'analizado' => 'success',
                        default => 'secondary'
                    };
                @endphp

                <div class="card mb-4 shadow-sm priority-group priority-highlight">
                    <div class="card-header table-{{ $badgeClassMuestra }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none p-0 me-2" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" 
                                        aria-expanded="false" 
                                        aria-controls="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}"
                                        onclick="toggleChevron('chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}')">
                                    <x-heroicon-o-chevron-up id="chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                                </button>
                                <div>
                                    <h4 class="mb-0 text-primary">
                                        Cotización Nº {{ $isLista ? $key : explode('_', $key)[0] }} 
                                        @if($isLista)
                                            - ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoGrupo) }}
                                        </span>
                                        <span class="badge priority-badge">
                                            <x-heroicon-o-star class="me-1" style="width: 14px; height: 14px;" />
                                            Prioridad
                                        </span>
                                        @if($isLista && $grupo['instancias'][0]['instancia_muestra']->es_frecuente && $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a class="btn btn-outline-primary btn-sm"
                                href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli ?? '' }}, {{ $cotizacion->coti_localidad ?? '' }}, {{ $cotizacion->coti_partido ?? '' }}">
                                    <x-heroicon-o-map class="me-1" style="width: 16px; height: 16px;" />
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>

                        @if($cotizacion)
                            <div class="mt-3 small">
                                <div class="row g-2">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Fecha: </strong> 
                                        @if($isLista)
                                            {{ \Carbon\Carbon::parse($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @else
                                            {{ \Carbon\Carbon::parse($grupo['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="w-60">Descripción</th>
                                            <th class="w-40">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isLista)
                                            @foreach($grupo['instancias'] as $instancia)
                                                @php
                                                    $muestra = $instancia['muestra'];
                                                    $instanciaMuestra = $instancia['instancia_muestra'];
                                                    $analisis = $instancia['analisis'];
                                                    $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                    $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                    $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                    $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassMuestra = match ($estadoMuestra) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $isPriority = $instancia['is_priority'] ?? false;
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if($isPriority)
                                                                    <span class="badge priority-badge me-2">
                                                                        <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                        Prioritaria
                                                                    </span>
                                                                @endif
                                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} (#{{ $instanciaMuestra->instance_number }})</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                                @if($esFrecuente && $frecuenciaDias > 0)
                                                                    <span class="badge bg-light text-dark border mt-1">
                                                                        <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                        Cada {{ $frecuenciaDias }} días
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                               class="btn btn-sm btn-dark">
                                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                            {{ ucfirst($estadoMuestra) }}
                                                        </span>
                                                    </td>
                                                </tr>

                                                                                                 @foreach($analisis as $tarea)
                                                    @php
                                                        $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                        $badgeClassAnalisis = match ($estado) {
                                                            'coordinado', 'coordinado analisis' => 'table-warning',
                                                            'en revision analisis' => 'table-info',
                                                            'analizado' => 'table-success',
                                                            'suspension' => 'table-danger text-white',
                                                            default => 'table-secondary'
                                                        };
                                                    @endphp
                                                    <tr class="{{ $badgeClassAnalisis }}">
                                                        <td class="small">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                    <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                    @if($tarea->resultado)
                                                                        <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                                {{ ucfirst($estado) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $muestra = $grupo['muestra'];
                                                $instanciaMuestra = $grupo['instancia_muestra'];
                                                $analisis = $grupo['analisis'];
                                                $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassMuestra = match ($estadoMuestra) {
                                                    'coordinado', 'coordinado analisis' => 'table-warning',
                                                    'en revision analisis' => 'table-info',
                                                    'analizado' => 'table-success',
                                                    'suspension' => 'table-danger text-white',
                                                    default => 'table-secondary'
                                                };
                                                $isPriority = $grupo['is_priority'] ?? false;
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @if($isPriority)
                                                                <span class="badge priority-badge me-2">
                                                                    <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                    Prioritaria
                                                                </span>
                                                            @endif
                                                            <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                            <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                            @if($esFrecuente && $frecuenciaDias > 0)
                                                                <span class="badge bg-light text-dark border mt-1">
                                                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                    Cada {{ $frecuenciaDias }} días
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                           class="btn btn-sm btn-dark">
                                                            <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                            Ver
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                        {{ ucfirst($estadoMuestra) }}
                                                    </span>
                                                </td>
                                            </tr>

                                                                                             @foreach($analisis as $tarea)
                                                @php
                                                    $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassAnalisis = match ($estado) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                @endphp
                                                <tr class="{{ $badgeClassAnalisis }}">
                                                    <td class="small">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                @if($tarea->resultado)
                                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                            {{ ucfirst($estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif



    @if(count($gruposCoordinados) > 0)
        <div style="margin-bottom: 40px;">
            <h3 class="text-primary mb-3">
                <x-heroicon-o-clipboard-document-check class="me-2" style="width: 24px; height: 24px;" />
                Órdenes Pendientes ({{ count($gruposCoordinados) }})
            </h3>
            
            @foreach($gruposCoordinados as $grupoData)
                @php
                    $grupo = $grupoData['grupo'];
                    $key = $grupoData['key'];
                    $isLista = $grupoData['isLista'];
                    $hasPriority = $grupoData['hasPriority'];
                    $cotizacion = $cotizaciones->get($isLista ? $key : explode('_', $key)[0]);
                    
                    // Estado para el encabezado
                    $estadoMuestra = $isLista
                        ? strtolower($grupo['instancias'][0]['instancia_muestra']->cotio_estado_analisis ?? 'pendiente')
                        : strtolower($grupo['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
                    $badgeClassMuestra = match ($estadoMuestra) {
                        'coordinado', 'coordinado analisis' => 'warning',
                        'en revision analisis' => 'info',
                        'analizado' => 'success',
                        'suspension' => 'danger',
                        default => 'secondary'
                    };
                @endphp

                <div class="card mb-4 shadow-sm @if($hasPriority) priority-group priority-highlight @endif">
                    <div class="card-header table-{{ $badgeClassMuestra }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none p-0 me-2" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" 
                                        aria-expanded="false" 
                                        aria-controls="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}"
                                        onclick="toggleChevron('chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}')">
                                    <x-heroicon-o-chevron-up id="chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                                </button>
                                <div>
                                    <h4 class="mb-0 text-primary">
                                        Cotización Nº {{ $isLista ? $key : explode('_', $key)[0] }} 
                                        @if($isLista)
                                            - ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
                                        @if($hasPriority)
                                            <span class="badge priority-badge">
                                                <x-heroicon-o-star class="me-1" style="width: 14px; height: 14px;" />
                                                Prioridad
                                            </span>
                                        @endif
                                        @if($isLista && $grupo['instancias'][0]['instancia_muestra']->es_frecuente && $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a class="btn btn-outline-primary btn-sm"
                                   href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli ?? '' }}, {{ $cotizacion->coti_localidad ?? '' }}, {{ $cotizacion->coti_partido ?? '' }}">
                                    <x-heroicon-o-map class="me-1" style="width: 16px; height: 16px;" />
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>

                        @if($cotizacion)
                            <div class="mt-3 small">
                                <div class="row g-2">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Fecha: </strong> 
                                        @if($isLista)
                                            {{ \Carbon\Carbon::parse($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @else
                                            {{ \Carbon\Carbon::parse($grupo['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="w-60">Descripción</th>
                                            <th class="w-40">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isLista)
                                            @foreach($grupo['instancias'] as $instancia)
                                                @php
                                                    $muestra = $instancia['muestra'];
                                                    $instanciaMuestra = $instancia['instancia_muestra'];
                                                    $analisis = $instancia['analisis'];
                                                    $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                    $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                    $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                    $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassMuestra = match ($estadoMuestra) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $isPriority = $instancia['is_priority'] ?? false;
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if($isPriority)
                                                                    <span class="badge priority-badge me-2">
                                                                        <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                        Prioritaria
                                                                    </span>
                                                                @endif
                                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} (#{{ $instanciaMuestra->instance_number }})</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                                @if($esFrecuente && $frecuenciaDias > 0)
                                                                    <span class="badge bg-light text-dark border mt-1">
                                                                        <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                        Cada {{ $frecuenciaDias }} días
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                               class="btn btn-sm btn-dark">
                                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                            {{ ucfirst($estadoMuestra) }}
                                                        </span>
                                                    </td>
                                                </tr>

                                                                                                 @foreach($analisis as $tarea)
                                                    @php
                                                        $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                        $badgeClassAnalisis = match ($estado) {
                                                            'coordinado', 'coordinado analisis' => 'table-warning',
                                                            'en revision analisis' => 'table-info',
                                                            'analizado' => 'table-success',
                                                            'suspension' => 'table-danger text-white',
                                                            default => 'table-secondary'
                                                        };
                                                    @endphp
                                                    <tr class="{{ $badgeClassAnalisis }}">
                                                        <td class="small">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                    <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                    @if($tarea->resultado)
                                                                        <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                                {{ ucfirst($estado) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $muestra = $grupo['muestra'];
                                                $instanciaMuestra = $grupo['instancia_muestra'];
                                                $analisis = $grupo['analisis'];
                                                $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassMuestra = match ($estadoMuestra) {
                                                    'coordinado', 'coordinado analisis' => 'table-warning',
                                                    'en revision analisis' => 'table-info',
                                                    'analizado' => 'table-success',
                                                    'suspension' => 'table-danger text-white',
                                                    default => 'table-secondary'
                                                };
                                                $isPriority = $grupo['is_priority'] ?? false;
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @if($isPriority)
                                                                <span class="badge priority-badge me-2">
                                                                    <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                    Prioritaria
                                                                </span>
                                                            @endif
                                                            <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                            <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                            @if($esFrecuente && $frecuenciaDias > 0)
                                                                <span class="badge bg-light text-dark border mt-1">
                                                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                    Cada {{ $frecuenciaDias }} días
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                           class="btn btn-sm btn-dark">
                                                            <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                            Ver
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                        {{ ucfirst($estadoMuestra) }}
                                                    </span>
                                                </td>
                                            </tr>

                                                                                             @foreach($analisis as $tarea)
                                                @php
                                                    $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassAnalisis = match ($estado) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                @endphp
                                                <tr class="{{ $badgeClassAnalisis }}">
                                                    <td class="small">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                @if($tarea->resultado)
                                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                            {{ ucfirst($estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Sección de Órdenes en Revisión -->
    @if(count($gruposEnRevision) > 0)
        <div style="margin-bottom: 40px;">
            <h3 class="text-primary mb-3">
                <x-heroicon-o-magnifying-glass class="me-2" style="width: 24px; height: 24px;" />
                Órdenes en Revisión ({{ count($gruposEnRevision) }})
            </h3>
            @foreach($gruposEnRevision as $grupoData)
                @php
                    $grupo = $grupoData['grupo'];
                    $key = $grupoData['key'];
                    $isLista = $grupoData['isLista'];
                    $hasPriority = $grupoData['hasPriority'];
                    $cotizacion = $cotizaciones->get($isLista ? $key : explode('_', $key)[0]);
                    
                    // Estado para el encabezado
                    $estadoMuestra = $isLista
                        ? strtolower($grupo['instancias'][0]['instancia_muestra']->cotio_estado_analisis ?? 'pendiente')
                        : strtolower($grupo['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
                    $badgeClassMuestra = match ($estadoMuestra) {
                        'coordinado', 'coordinado analisis' => 'warning',
                        'en revision analisis' => 'info',
                        'analizado' => 'success',
                        'suspension' => 'danger',
                        default => 'secondary'
                    };
                @endphp

                <div class="card mb-4 shadow-sm @if($hasPriority) priority-group @endif">
                    <div class="card-header table-{{ $badgeClassMuestra }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none p-0 me-2" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" 
                                        aria-expanded="false" 
                                        aria-controls="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}"
                                        onclick="toggleChevron('chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}')">
                                    <x-heroicon-o-chevron-up id="chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                                </button>
                                <div>
                                    <h4 class="mb-0 text-primary">
                                        Cotización Nº {{ $isLista ? $key : explode('_', $key)[0] }} 
                                        @if($isLista)
                                            - ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
                                        @if($hasPriority)
                                            <span class="badge priority-badge">
                                                <x-heroicon-o-star class="me-1" style="width: 14px; height: 14px;" />
                                                Prioridad
                                            </span>
                                        @endif
                                        @if($isLista && $grupo['instancias'][0]['instancia_muestra']->es_frecuente && $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @elseif(!$isLista && $grupo['instancia_muestra']->es_frecuente && $grupo['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a class="btn btn-outline-primary btn-sm"
                                   href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli ?? '' }}, {{ $cotizacion->coti_localidad ?? '' }}, {{ $cotizacion->coti_partido ?? '' }}">
                                    <x-heroicon-o-map class="me-1" style="width: 16px; height: 16px;" />
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>

                        @if($cotizacion)
                            <div class="mt-3 small">
                                <div class="row g-2">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Fecha: </strong> 
                                        @if($isLista)
                                            {{ \Carbon\Carbon::parse($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @else
                                            {{ \Carbon\Carbon::parse($grupo['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="w-60">Descripción</th>
                                            <th class="w-40">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isLista)
                                            @foreach($grupo['instancias'] as $instancia)
                                                @php
                                                    $muestra = $instancia['muestra'];
                                                    $instanciaMuestra = $instancia['instancia_muestra'];
                                                    $analisis = $instancia['analisis'];
                                                    $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                    $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                    $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                    $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassMuestra = match ($estadoMuestra) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $isPriority = $instancia['is_priority'] ?? false;
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if($isPriority)
                                                                    <span class="badge priority-badge me-2">
                                                                        <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                        Prioritaria
                                                                    </span>
                                                                @endif
                                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} (#{{ $instanciaMuestra->instance_number }})</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                                @if($esFrecuente && $frecuenciaDias > 0)
                                                                    <span class="badge bg-light text-dark border mt-1">
                                                                        <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                        Cada {{ $frecuenciaDias }} días
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                               class="btn btn-sm btn-dark">
                                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                            {{ ucfirst($estadoMuestra) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                                                                 @foreach($analisis as $tarea)
                                                    @php
                                                        $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                        $badgeClassAnalisis = match ($estado) {
                                                            'coordinado', 'coordinado analisis' => 'table-warning',
                                                            'en revision analisis' => 'table-info',
                                                            'analizado' => 'table-success',
                                                            'suspension' => 'table-danger text-white',
                                                            default => 'table-secondary'
                                                        };
                                                    @endphp
                                                    <tr class="{{ $badgeClassAnalisis }}">
                                                        <td class="small">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                    <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                    @if($tarea->resultado)
                                                                        <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                                {{ ucfirst($estado) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $muestra = $grupo['muestra'];
                                                $instanciaMuestra = $grupo['instancia_muestra'];
                                                $analisis = $grupo['analisis'];
                                                $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassMuestra = match ($estadoMuestra) {
                                                    'coordinado', 'coordinado analisis' => 'table-warning',
                                                    'en revision analisis' => 'table-info',
                                                    'analizado' => 'table-success',
                                                    'suspension' => 'table-danger text-white',
                                                    default => 'table-secondary'
                                                };
                                                $isPriority = $grupo['is_priority'] ?? false;
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @if($isPriority)
                                                                <span class="badge priority-badge me-2">
                                                                    <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                    Prioritaria
                                                                </span>
                                                            @endif
                                                            <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                            <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                            @if($esFrecuente && $frecuenciaDias > 0)
                                                                <span class="badge bg-light text-dark border mt-1">
                                                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                    Cada {{ $frecuenciaDias }} días
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                           class="btn btn-sm btn-dark">
                                                            <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                            Ver
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                        {{ ucfirst($estadoMuestra) }}
                                                    </span>
                                                </td>
                                            </tr>

                                                                                             @foreach($analisis as $tarea)
                                                @php
                                                    $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassAnalisis = match ($estado) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                @endphp
                                                <tr class="{{ $badgeClassAnalisis }}">
                                                    <td class="small">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                @if($tarea->resultado)
                                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                            {{ ucfirst($estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Sección de Órdenes Finalizadas -->
    @if(count($gruposFinalizados) > 0)
        <div style="margin-bottom: 40px;">
            <h3 class="text-primary mb-3">
                <x-heroicon-o-check-circle class="me-2" style="width: 24px; height: 24px;" />
                Órdenes Finalizadas ({{ count($gruposFinalizados) }})
            </h3>
            @foreach($gruposFinalizados as $grupoData)
                @php
                    $grupo = $grupoData['grupo'];
                    $key = $grupoData['key'];
                    $isLista = $grupoData['isLista'];
                    $hasPriority = $grupoData['hasPriority'];
                    $cotizacion = $cotizaciones->get($isLista ? $key : explode('_', $key)[0]);
                    
                    // Estado para el encabezado
                    $estadoMuestra = $isLista
                        ? strtolower($grupo['instancias'][0]['instancia_muestra']->cotio_estado_analisis ?? 'pendiente')
                        : strtolower($grupo['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
                    $badgeClassMuestra = match ($estadoMuestra) {
                        'coordinado', 'coordinado analisis' => 'warning',
                        'en revision analisis' => 'info',
                        'analizado' => 'success',
                        'suspension' => 'danger',
                        default => 'secondary'
                    };
                @endphp

                <div class="card mb-4 shadow-sm @if($hasPriority) priority-group @endif">
                    <div class="card-header table-{{ $badgeClassMuestra }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none p-0 me-2" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" 
                                        aria-expanded="false" 
                                        aria-controls="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}"
                                        onclick="toggleChevron('chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}')">
                                    <x-heroicon-o-chevron-up id="chevron-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                                </button>
                                <div>
                                    <h4 class="mb-0 text-primary">
                                        Cotización Nº {{ $isLista ? $key : explode('_', $key)[0] }} 
                                        @if($isLista)
                                            - ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
                                        @if($hasPriority)
                                            <span class="badge priority-badge">
                                                <x-heroicon-o-star class="me-1" style="width: 14px; height: 14px;" />
                                                Prioridad
                                            </span>
                                        @endif
                                        @if($isLista && $grupo['instancias'][0]['instancia_muestra']->es_frecuente && $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancias'][0]['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @elseif(!$isLista && $grupo['instancia_muestra']->es_frecuente && $grupo['instancia_muestra']->frecuencia_dias > 0)
                                            <span class="badge bg-light text-dark border">
                                                <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                Cada {{ $grupo['instancia_muestra']->frecuencia_dias }} días
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a class="btn btn-outline-primary btn-sm"
                                   href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli ?? '' }}, {{ $cotizacion->coti_localidad ?? '' }}, {{ $cotizacion->coti_partido ?? '' }}">
                                    <x-heroicon-o-map class="me-1" style="width: 16px; height: 16px;" />
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>

                        @if($cotizacion)
                            <div class="mt-3 small">
                                <div class="row g-2">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Fecha: </strong> 
                                        @if($isLista)
                                            {{ \Carbon\Carbon::parse($grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @else
                                            {{ \Carbon\Carbon::parse($grupo['instancia_muestra']->fecha_inicio_ot)->format('d/m/Y') ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="tabla-{{ $isLista ? $key : explode('_', $key)[0] . '-' . explode('_', $key)[1] }}" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="w-60">Descripción</th>
                                            <th class="w-40">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isLista)
                                            @foreach($grupo['instancias'] as $instancia)
                                                @php
                                                    $muestra = $instancia['muestra'];
                                                    $instanciaMuestra = $instancia['instancia_muestra'];
                                                    $analisis = $instancia['analisis'];
                                                    $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                    $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                    $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                    $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassMuestra = match ($estadoMuestra) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                    $isPriority = $instancia['is_priority'] ?? false;
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if($isPriority)
                                                                    <span class="badge priority-badge me-2">
                                                                        <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                        Prioritaria
                                                                    </span>
                                                                @endif
                                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} (#{{ $instanciaMuestra->instance_number }})</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                                @if($esFrecuente && $frecuenciaDias > 0)
                                                                    <span class="badge bg-light text-dark border mt-1">
                                                                        <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                        Cada {{ $frecuenciaDias }} días
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                               class="btn btn-sm btn-dark">
                                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                            {{ ucfirst($estadoMuestra) }}
                                                        </span>
                                                    </td>
                                                </tr>

                                                                                                 @foreach($analisis as $tarea)
                                                    @php
                                                        $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                        $badgeClassAnalisis = match ($estado) {
                                                            'coordinado', 'coordinado analisis' => 'table-warning',
                                                            'en revision analisis' => 'table-info',
                                                            'analizado' => 'table-success',
                                                            'suspension' => 'table-danger text-white',
                                                            default => 'table-secondary'
                                                        };
                                                    @endphp
                                                    <tr class="{{ $badgeClassAnalisis }}">
                                                        <td class="small">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                    <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                    @if($tarea->resultado)
                                                                        <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                                {{ ucfirst($estado) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $muestra = $grupo['muestra'];
                                                $instanciaMuestra = $grupo['instancia_muestra'];
                                                $analisis = $grupo['analisis'];
                                                $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
                                                $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
                                                $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
                                                $estadoMuestra = strtolower($instanciaMuestra->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassMuestra = match ($estadoMuestra) {
                                                    'coordinado', 'coordinado analisis' => 'table-warning',
                                                    'en revision analisis' => 'table-info',
                                                    'analizado' => 'table-success',
                                                    'suspension' => 'table-danger text-white',
                                                    default => 'table-secondary'
                                                };
                                                $isPriority = $grupo['is_priority'] ?? false;
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }} @if($isPriority) priority-instance @endif">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @if($isPriority)
                                                                <span class="badge priority-badge me-2">
                                                                    <x-heroicon-o-star class="me-1" style="width: 12px; height: 12px;" />
                                                                    Prioritaria
                                                                </span>
                                                            @endif
                                                            <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                            <small class="text-muted d-block mt-1">ID: {{ str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                            @if($esFrecuente && $frecuenciaDias > 0)
                                                                <span class="badge bg-light text-dark border mt-1">
                                                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                                                    Cada {{ $frecuenciaDias }} días
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) }}" 
                                                           class="btn btn-sm btn-dark">
                                                            <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                            Ver
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                                        {{ ucfirst($estadoMuestra) }}
                                                    </span>
                                                </td>
                                            </tr>

                                                                                             @foreach($analisis as $tarea)
                                                @php
                                                    $estado = strtolower($tarea->cotio_estado_analisis ?? 'pendiente');
                                                    $badgeClassAnalisis = match ($estado) {
                                                        'coordinado', 'coordinado analisis' => 'table-warning',
                                                        'en revision analisis' => 'table-info',
                                                        'analizado' => 'table-success',
                                                        'suspension' => 'table-danger text-white',
                                                        default => 'table-secondary'
                                                    };
                                                @endphp
                                                <tr class="{{ $badgeClassAnalisis }}">
                                                    <td class="small">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                                <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small>
                                                                @if($tarea->resultado)
                                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                                            {{ ucfirst($estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@else
    <div class="alert alert-info">
        No hay órdenes para mostrar.
    </div>
@endif

<div class="d-flex justify-content-center mt-4">
    @if($tareasPaginadas instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $tareasPaginadas->links() }}
    @endif
</div>

<style>
    .chevron-icon {
        transition: transform 0.3s ease;
    }
    .chevron-icon.rotated {
        transform: rotate(180deg);
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .table-light {
        background-color: rgba(248, 249, 250, 0.8) !important;
    }
    .table-warning {
        background-color: rgba(255, 243, 205, 0.8) !important;
    }
    .table-info {
        background-color: rgba(209, 236, 241, 0.8) !important;
    }
    .table-success {
        background-color: rgba(212, 237, 218, 0.8) !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
    }
    .bg-info {
        background-color: #0dcaf0 !important;
    }
    .bg-success {
        background-color: #198754 !important;
    }
    .card-header {
        padding: 1rem 1.25rem;
    }

    /* Existing styles unchanged */
    .priority-group {
        border-left: 4px solid #ffc107;
        background-color: rgba(255, 193, 7, 0.1);
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.2);
    }
    
    .priority-instance {
        position: relative;
        padding-left: 30px !important;
    }
    
    .priority-badge {
        background-color: #ffc107;
        color: #000;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .table-warning.priority-instance {
        background-color: rgba(255, 243, 205, 0.9) !important;
    }
    .table-info.priority-instance {
        background-color: rgba(209, 236, 241, 0.9) !important;
    }
    .table-success.priority-instance {
        background-color: rgba(212, 237, 218, 0.9) !important;
    }
    
    .priority-highlight {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    /* New style for Revisión de Resultados */
    .revision-resultados-group {
        border-left: 4px solid #0d6efd; /* Primary blue border */
        background-color: rgba(13, 110, 253, 0.1);
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.2);
    }

    .revision-resultados-badge {
        background-color: #0d6efd; /* Primary blue */
        color: #fff;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>

<script>
    function toggleChevron(iconId) {
        const icon = document.getElementById(iconId);
        if (icon) {
            icon.classList.toggle('rotated');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.collapse.show').forEach(collapseElement => {
            const targetId = collapseElement.id;
            const iconId = `chevron-${targetId.replace('tabla-', '')}`;
            const icon = document.getElementById(iconId);
            if (icon) {
                icon.classList.add('rotated');
            }
        });
        
        // Auto-scroll to first priority group if exists
        const firstPriorityGroup = document.querySelector('.priority-group');
        if (firstPriorityGroup) {
            firstPriorityGroup.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
</script>