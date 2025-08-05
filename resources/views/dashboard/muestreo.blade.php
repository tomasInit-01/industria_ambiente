@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Panel de Muestreo</h1>
        <div class="text-muted">{{ now()->format('l, d F Y') }}</div>
    </div>

    {{-- Resumen General --}}
    <div class="row mb-4 g-4">
        <div class="col-xl-3 col-md-6">
            <a href="{{ request()->fullUrlWithQuery(['estado' => 'all']) }}" class="text-decoration-none">
                <div class="card bg-primary bg-gradient text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-uppercase small">Total Muestras</h5>
                                <p class="card-text display-6 fw-bold">{{ $totalMuestras }}</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <x-heroicon-o-beaker style="width: 20px; height: 20px;"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="small">Asignadas a mi equipo</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ request()->fullUrlWithQuery(['estado' => 'coordinado muestreo']) }}" class="text-decoration-none">
                <div class="card bg-warning bg-gradient text-dark h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-uppercase small">Pendientes</h5>
                                <p class="card-text display-6 fw-bold">{{ $pendientes }}</p>
                            </div>
                            <div class="bg-dark bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <x-heroicon-o-clock style="width: 20px; height: 20px;"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="small">Por procesar</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ request()->fullUrlWithQuery(['estado' => 'en revision muestreo']) }}" class="text-decoration-none">
                <div class="card bg-info bg-gradient text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-uppercase small">En Proceso</h5>
                                <p class="card-text display-6 fw-bold">{{ $enProceso }}</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <x-heroicon-o-arrow-path style="width: 20px; height: 20px;"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="small">En revisión</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ request()->fullUrlWithQuery(['estado' => 'muestreado']) }}" class="text-decoration-none">
                <div class="card bg-success bg-gradient text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-uppercase small">Finalizadas</h5>
                                <p class="card-text display-6 fw-bold">{{ $finalizadas }}</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <x-heroicon-o-check-circle style="width: 20px; height: 20px;"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="small">Completadas</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="row g-4">
        {{-- Muestras asignadas --}}
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Muestras Asignadas</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item filter-option" href="{{ request()->fullUrlWithQuery(['estado' => 'all']) }}">Todos</a></li>
                                <li><a class="dropdown-item filter-option" href="{{ request()->fullUrlWithQuery(['estado' => 'coordinado muestreo']) }}">Pendientes</a></li>
                                <li><a class="dropdown-item filter-option" href="{{ request()->fullUrlWithQuery(['estado' => 'en revision muestreo']) }}">En proceso</a></li>
                                <li><a class="dropdown-item filter-option" href="{{ request()->fullUrlWithQuery(['estado' => 'muestreado']) }}">Finalizados</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-option" href="{{ request()->fullUrlWithQuery(['estado' => 'proximos']) }}">Próximos 3 días</a></li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Muestras asignadas a mi o a mi equipo</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Cotización</th>
                                    <th>Descripción</th>
                                    <th>Fecha Muestreo</th>
                                    <th>Responsables</th>
                                    <th class="pe-4 d-flex flex-column align-items-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($muestras as $muestra)
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <a href="/show/{{ $muestra->cotizacion->coti_num }}" class="text-primary">
                                            {{ $muestra->cotizacion->coti_num ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td style="max-width: 200px;" title="{{ $muestra->cotio_descripcion }} - Muestra {{ $muestra->id ? '#' . str_pad($muestra->id, 8, '0', STR_PAD_LEFT) : null }}">
                                        <a href="{{ route('muestras.ver', [
                                            'cotizacion' => $muestra->cotizacion->coti_num,
                                            'item' => $muestra->cotio_item,
                                            'instance' => $muestra->instance_number
                                        ]) }}" class="text-primary">
                                            {{ $muestra->cotio_descripcion }} - Muestra {{ $muestra->id ? '#' . str_pad($muestra->id, 8, '0', STR_PAD_LEFT) : null }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ $muestra->fecha_muestreo->format('d/m/Y') }}</span>
                                        <small class="text-muted">{{ $muestra->fecha_muestreo->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($muestra->responsablesMuestreo->count() > 0)
                                            <div class="avatar-group">
                                                @foreach($muestra->responsablesMuestreo as $responsable)
                                                <span class="avatar avatar-xs" data-bs-toggle="tooltip" title="{{ $responsable->usu_descripcion }}">
                                                    {{ substr($responsable->usu_descripcion, 0, 1) }}{{ substr(strstr($responsable->usu_descripcion, ' '), 1, 1) }}
                                                </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        @php
                                            $badgeColor = match($muestra->cotio_estado) {
                                                'coordinado muestreo' => 'warning text-dark',
                                                'en revision muestreo' => 'info text-dark',
                                                'suspension' => 'danger text-white',
                                                'muestreado' => 'success',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <div class="d-flex flex-column justify-content-center align-items-center gap-1">
                                            <span class="badge rounded-pill bg-{{ $badgeColor }} text-capitalize">
                                                {{ str_replace('_', ' ', $muestra->cotio_estado) }}
                                            </span>
                                            
                                            @if($muestra->enable_ot)
                                                <small class="badge bg-info text-white px-2 py-1 rounded-pill">
                                                    En OT
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                        <p class="mb-0">No hay muestras asignadas actualmente</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Mostrando {{ $muestras->firstItem() ?? 0 }} a {{ $muestras->lastItem() ?? 0 }} de {{ $muestras->total() }} muestras
                            </div>
                            <div>
                                {{ $muestras->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar con información complementaria --}}
        <div class="col-lg-4">
            {{-- Muestras próximas --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Estados de Muestras</h5>
                    <p class="text-muted small mb-0">Distribución por estado</p>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 200px;">
                        <canvas id="estadoMuestrasChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span>Pendientes</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ $pendientes }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span>En Proceso</span>
                                <span class="badge bg-info text-dark rounded-pill">{{ $enProceso }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span>Finalizadas</span>
                                <span class="badge bg-success rounded-pill">{{ $finalizadas }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Muestras próximas --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Muestras Próximas</h5>
                    <p class="text-muted small mb-0">Próximos 3 días</p>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($muestrasProximas as $muestra)
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold">#{{ $muestra->cotizacion->coti_num ?? 'N/A' }}</span>
                                <small class="text-muted">{{ $muestra->fecha_muestreo->format('d/m H:i') }}</small>
                            </div>
                            <p class="mb-1 small text-truncate">{{ $muestra->cotio_descripcion }} {{ $muestra->id ? '#' . str_pad($muestra->id, 8, '0', STR_PAD_LEFT) : null }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $muestra->cotio_estado == 'coordinado muestreo' ? 'warning text-dark' : ($muestra->cotio_estado == 'en revision muestreo' ? 'info text-dark' : 'success') }} small">
                                    {{ Str::title(str_replace('_', ' ', $muestra->cotio_estado)) }}
                                </span>
                                @if($muestra->vehiculo)
                                <span class="badge bg-light text-dark small">
                                    <i class="fas fa-car me-1"></i> {{ $muestra->vehiculo->patente }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <p class="mb-0 small">No hay muestras programadas para los próximos 3 días</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Vehículos asignados --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Vehículos Asignados</h5>
                    <p class="text-muted small mb-0">En uso por tu equipo</p>
                </div>
                <div class="card-body">
                    @forelse($vehiculosAsignados as $vehiculo)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-light rounded p-2 me-3">
                            <i class="fas fa-car text-primary fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</h6>
                            <small class="text-muted">Patente: {{ $vehiculo->patente }}</small>
                        </div>
                        {{-- <span class="badge bg-info text-dark">
                            {{ $vehiculo->cotioInstancias->where('cotio_estado', '!=', 'finalizado')->count() }} muestras
                        </span> --}}
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-car-side fa-2x mb-2"></i>
                        <p class="mb-0 small">No hay vehículos asignados actualmente</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Herramientas en uso --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Herramientas en Uso</h5>
                    <p class="text-muted small mb-0">Equipamiento asignado</p>
                </div>
                <div class="card-body">
                    @forelse($herramientasEnUso as $herramienta)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-light rounded p-2 me-3">
                            <i class="fas fa-tools text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $herramienta->nombre }}</h6>
                            <small class="text-muted">Serial: {{ $herramienta->serial }}</small>
                        </div>
                        <span class="badge bg-light text-dark">
                            {{ $herramienta->cotio_instancias_count }} uso(s)
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2"></i>
                        <p class="mb-0 small">No hay herramientas en uso actualmente</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    
        // Actualizar el texto del botón dropdown según el estado actual
        const currentEstado = '{{ $estadoFiltro }}';
        const filterDropdown = document.getElementById('filterDropdown');
        const filterOptions = document.querySelectorAll('.filter-option');
        
        filterOptions.forEach(option => {
            if (option.getAttribute('href').includes(`estado=${currentEstado}`)) {
                filterDropdown.innerHTML = `<i class="fas fa-filter me-1"></i> ${option.textContent}`;
            }
        });

        // Verificar si el canvas existe
        const canvas = document.getElementById('estadoMuestrasChart');
        if (!canvas) {
            console.error('No se encontró el elemento canvas para el gráfico');
            return;
        }

        // Verificar los datos
        const datosGrafico = {
            pendientes: {{ $pendientes }},
            enProceso: {{ $enProceso }},
            finalizadas: {{ $finalizadas }}
        };
        // console.log('Datos del gráfico:', datosGrafico);

        // Gráfico de estados
        try {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'En Proceso', 'Finalizadas'],
                    datasets: [{
                        data: [
                            datosGrafico.pendientes,
                            datosGrafico.enProceso,
                            datosGrafico.finalizadas
                        ],
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(13, 202, 240, 0.7)',
                            'rgba(25, 135, 84, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 193, 7, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(25, 135, 84, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        } catch (error) {
            console.error('Error al crear el gráfico:', error);
        }
    });
</script>

@endsection