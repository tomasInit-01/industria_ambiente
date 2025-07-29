<?php 
// dd($tareasAgrupadas);
?>

@foreach($tareasAgrupadas as $key => $grupo)
    @php
        // Extraer componentes de la clave
        [$numCoti, $instanceNumber, $itemId] = explode('_', $key);
        $cotizacion = $cotizaciones->get($numCoti);
        $muestra = $grupo['muestra'];
        $instanciaMuestra = $grupo['instancia_muestra'];
        $analisis = $grupo['analisis'];
        
        // Datos para la muestra
        $vehiculoAsignado = $instanciaMuestra->vehiculo ?? null;
        $esFrecuente = $instanciaMuestra->es_frecuente ?? false;
        $frecuenciaDias = $instanciaMuestra->frecuencia_dias ?? 0;
        $estadoMuestra = strtolower($instanciaMuestra->cotio_estado ?? 'pendiente');
        $badgeClassMuestra = match ($estadoMuestra) {
            'coordinado muestreo' => 'warning',
            'en revision muestreo' => 'info',
            'muestreado' => 'success',
            'suspension' => 'danger',
            default => 'secondary'
        };
    @endphp

    <div class="card mb-4 shadow-sm">
        <div class="card-header table-{{ $badgeClassMuestra }}">
            <!-- Encabezado mejorado -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-decoration-none p-0 me-2" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#tabla-{{ $numCoti }}-{{ $instanceNumber }}" 
                            aria-expanded="false" 
                            aria-controls="tabla-{{ $numCoti }}-{{ $instanceNumber }}"
                            onclick="toggleChevron('chevron-{{ $numCoti }}-{{ $instanceNumber }}')">
                        <x-heroicon-o-chevron-up id="chevron-{{ $numCoti }}-{{ $instanceNumber }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                    </button>
                    <div>
                        <h4 class="mb-0 text-primary">
                            Cotización Nº {{ $numCoti }} - {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }} {{ $instanciaMuestra->id ? '#' . str_pad($instanciaMuestra->id, 8, '0', STR_PAD_LEFT) : null }} (#{{ $instanciaMuestra->instance_number ?? ''}})
                        </h4>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-{{ $badgeClassMuestra }} text-dark">
                                {{ ucfirst($estadoMuestra) }}
                            </span>
                            @if($esFrecuente && $frecuenciaDias > 0)
                                <span class="badge bg-light text-dark border">
                                    <x-heroicon-o-arrow-path class="me-1" style="width: 14px; height: 14px;" />
                                    Cada {{ $frecuenciaDias }} días
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

            <!-- Información de la cotización -->
            @if($cotizacion)
                <div class="mt-3 small">
                    <div class="row g-2">
                        <div class="col-md-4 d-flex align-items-center">
                            <x-heroicon-o-calendar class="me-2 text-muted" style="width: 14px; height: 14px;" />
                            <strong>Fecha: </strong> {{ \Carbon\Carbon::parse($instanciaMuestra->fecha_inicio_muestreo)->format('d/m/Y') ?? 'N/A' }}
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

        <div id="tabla-{{ $numCoti }}-{{ $instanceNumber }}" class="collapse">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="w-60">Descripción</th>
                                {{-- <th class="w-40">Estado</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Fila de la muestra -->
                            @if($instanciaMuestra)
                                <tr class="fw-bold bg-{{ $badgeClassMuestra }}">
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span>MUESTRA: {{ $instanciaMuestra->cotio_descripcion ?? 'N/A' }}</span>
                                                {{-- <small class="text-muted d-block mt-1">ID: {{ $instanciaMuestra->id }}</small> --}}
                                            </div>
                                            <a href="{{ Auth::user()->rol == 'laboratorio' ? route('ordenes.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A']) : route('tareas.all.show', [$instanciaMuestra->cotio_numcoti ?? 'N/A', $instanciaMuestra->cotio_item ?? 'N/A', $instanciaMuestra->cotio_subitem ?? 'N/A', $instanciaMuestra->instance_number ?? 'N/A'])}}" 
                                               class="btn btn-sm btn-dark">
                                                <x-heroicon-o-eye class="me-1" style="width: 16px; height: 16px;" />
                                                Ver
                                            </a>
                                        </div>
                                    </td>
                                    {{-- <td class="text-center">
                                        <span class="badge text-dark {{ str_replace('table-', 'bg-', $badgeClassMuestra) }}">
                                            {{ ucfirst($estadoMuestra) }}
                                        </span>
                                    </td> --}}
                                </tr>
                            
                                {{-- @if($vehiculoAsignado)
                                    <tr class="table-light">
                                        <td colspan="2" class="text-uppercase small">
                                            <x-heroicon-o-truck class="me-1" style="width: 16px; height: 16px;" />
                                            <strong>Vehículo asignado:</strong> {{ $vehiculoAsignado->marca }} {{ $vehiculoAsignado->modelo }} ({{ $vehiculoAsignado->patente }})
                                        </td>
                                    </tr>
                                @endif --}}
                            @endif

                            <!-- Filas de análisis -->
                            @foreach($analisis as $tarea)
                                @php
                                    $estado = strtolower($tarea->cotio_estado ?? 'pendiente');
                                    $badgeClassAnalisis = match ($estado) {
                                        'coordinado muestreo' => 'table-warning',
                                        'en revision muestreo' => 'table-info',
                                        'muestreado' => 'table-success',
                                        'suspension' => 'table-danger text-white',
                                        default => 'table-secondary'
                                    };
                                @endphp
                                <tr class="{{ $badgeClassAnalisis }}">
                                    <td class="small">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span>ANÁLISIS: {{ $tarea->cotio_descripcion }}</span>
                                                {{-- <small class="text-muted d-block mt-1">ID: {{ $tarea->id }}</small> --}}
                                                @if($tarea->resultado)
                                                    <span class="badge bg-dark mt-1">RESULTADO: {{ $tarea->resultado }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-dark bg-{{ str_replace('table-', 'bg-', $badgeClassAnalisis) }}">
                                            {{ ucfirst($estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endforeach

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