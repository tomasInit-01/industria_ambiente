<?php
// Ordenar los grupos por estado y fecha
$gruposCoordinados = [];
$gruposEnRevision = [];
$gruposFinalizados = [];

foreach ($ordenesAgrupadas as $key => $grupo) {
    $isLista = $viewType === 'lista';
    $fechaMuestreo = $isLista 
        ? $grupo['instancias'][0]['instancia_muestra']->fecha_inicio_ot ?? null
        : $grupo['instancia_muestra']->fecha_inicio_ot ?? null;
    
    $estadoMuestra = $isLista
        ? strtolower($grupo['instancias'][0]['instancia_muestra']->cotio_estado_analisis ?? 'pendiente')
        : strtolower($grupo['instancia_muestra']->cotio_estado_analisis ?? 'pendiente');
    
    $grupoConFecha = [
        'grupo' => $grupo,
        'key' => $key,
        'fecha' => $fechaMuestreo ? \Carbon\Carbon::parse($fechaMuestreo) : null,
        'isLista' => $isLista
    ];
    
    if (in_array($estadoMuestra, ['coordinado', 'coordinado analisis'])) {
        $gruposCoordinados[] = $grupoConFecha;
    } elseif ($estadoMuestra === 'en revision analisis') {
        $gruposEnRevision[] = $grupoConFecha;
    } elseif ($estadoMuestra === 'analizado') {
        $gruposFinalizados[] = $grupoConFecha;
    }
}

// Funciones para ordenar por fecha (más recientes primero)
usort($gruposCoordinados, function($a, $b) {
    if ($a['fecha'] && $b['fecha']) {
        return $b['fecha'] <=> $a['fecha'];
    }
    return 0;
});

usort($gruposEnRevision, function($a, $b) {
    if ($a['fecha'] && $b['fecha']) {
        return $b['fecha'] <=> $a['fecha'];
    }
    return 0;
});

usort($gruposFinalizados, function($a, $b) {
    if ($a['fecha'] && $b['fecha']) {
        return $b['fecha'] <=> $a['fecha'];
    }
    return 0;
});
?>

@if(count($ordenesAgrupadas) > 0)
    <!-- Sección de Órdenes Coordinadas -->
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

                <div class="card mb-4 shadow-sm">
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
                                            - {{ $cotizacion->coti_empresa ?? 'N/A' }} ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
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
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-map-pin class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Dirección: </strong> {{ $cotizacion->coti_direccioncli ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-user-circle class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Cliente: </strong> {{ $cotizacion->coti_empresa ?? 'N/A' }}
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
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
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

                                                @if($vehiculoAsignado)
                                                    <tr class="table-light">
                                                        <td colspan="2" class="text-uppercase small">
                                                            <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                            <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                        </td>
                                                    </tr>
                                                @endif

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
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
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

                                            @if($vehiculoAsignado)
                                                <tr class="table-light">
                                                    <td colspan="2" class="text-uppercase small">
                                                        <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                        <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                    </td>
                                                </tr>
                                            @endif

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

                <div class="card mb-4 shadow-sm">
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
                                            - {{ $cotizacion->coti_empresa ?? 'N/A' }} ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
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
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-map-pin class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Dirección: </strong> {{ $cotizacion->coti_direccioncli ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-user-circle class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Cliente: </strong> {{ $cotizacion->coti_empresa ?? 'N/A' }}
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
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
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

                                                @if($vehiculoAsignado)
                                                    <tr class="table-light">
                                                        <td colspan="2" class="text-uppercase small">
                                                            <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                            <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                        </td>
                                                    </tr>
                                                @endif

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
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
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

                                            @if($vehiculoAsignado)
                                                <tr class="table-light">
                                                    <td colspan="2" class="text-uppercase small">
                                                        <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                        <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                    </td>
                                                </tr>
                                            @endif

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

                <div class="card mb-4 shadow-sm">
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
                                            - {{ $cotizacion->coti_empresa ?? 'N/A' }} ({{ $grupo['instancias']->count() }} Muestras)
                                        @else
                                            - {{ $grupo['instancia_muestra']->cotio_descripcion ?? 'N/A' }} {{ $grupo['instancia_muestra']->id ? '#' . str_pad($grupo['instancia_muestra']->id, 8, '0', STR_PAD_LEFT) : '' }} (#{{ $grupo['instancia_muestra']->instance_number ?? ''}})
                                        @endif
                                    </h4>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
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
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-map-pin class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Dirección: </strong> {{ $cotizacion->coti_direccioncli ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <x-heroicon-o-user-circle class="me-2 text-muted" style="width: 14px; height: 14px;" />
                                        <strong>Cliente: </strong> {{ $cotizacion->coti_empresa ?? 'N/A' }}
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
                                                @endphp

                                                <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
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

                                                @if($vehiculoAsignado)
                                                    <tr class="table-light">
                                                        <td colspan="2" class="text-uppercase small">
                                                            <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                            <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                        </td>
                                                    </tr>
                                                @endif

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
                                            @endphp

                                            <tr class="fw-bold {{ $badgeClassMuestra }}">
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
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

                                            @if($vehiculoAsignado)
                                                <tr class="table-light">
                                                    <td colspan="2" class="text-uppercase small">
                                                        <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                                        <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                                    </td>
                                                </tr>
                                            @endif

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
    });
</script>