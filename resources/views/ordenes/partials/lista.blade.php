
<div class="d-none d-lg-block">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="120">Orden</th>
                            <th>Cliente</th>
                            <th width="140" class="text-center">Progreso</th>
                            <th width="120" class="text-center">Fecha</th>
                            <th width="150">Matriz</th>
                            <th width="150" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ordenes as $numCoti => $instanciaData)
                        @php
                            $coti = $instanciaData['instancias']->first()->cotizacion;
                            
                            // Calcular estados para la barra de progreso
                            $analizadas = $instanciaData['instancias']->where('cotio_estado_analisis', 'analizado')->where('cotio_subitem', '=', 0)->count();
                            $enProceso = $instanciaData['instancias']->where('cotio_estado_analisis', 'en revision analisis')->where('cotio_subitem', '=', 0)->count();
                            $coordinadas = $instanciaData['instancias']->where('cotio_estado_analisis', 'coordinado analisis')->where('cotio_subitem', '=', 0)->count();
                            $total = $instanciaData['instancias']->where('cotio_subitem', '=', 0)->where('enable_ot', '=', 1)->count();
                            
                            $porcentajes = [
                                'analizadas' => $total > 0 ? ($analizadas / $total) * 100 : 0,
                                'en_proceso' => $total > 0 ? ($enProceso / $total) * 100 : 0,
                                'coordinadas' => $total > 0 ? ($coordinadas / $total) * 100 : 0,
                                'total' => $total > 0 ? (($analizadas + $enProceso + $coordinadas) / $total) * 100 : 0
                            ];

                            // Determinar estado predominante para mostrar
                            $estadoPredominante = $instanciaData['estado_predominante'] ?? 'pendiente_coordinar';
                            $badgeColorEstado = match($estadoPredominante) {
                                'coordinado analisis' => 'bg-warning text-dark',
                                'en revision analisis' => 'bg-info text-white',
                                'analizado' => 'bg-success text-white',
                                'suspension' => 'bg-danger text-white',
                                'pendiente_coordinar' => 'bg-secondary text-white',
                                default => 'bg-secondary text-white',
                            };
                            $estadoTexto = match($estadoPredominante) {
                                'coordinado analisis' => 'Coordinada',
                                'en revision analisis' => 'En Revisión',
                                'analizado' => 'Finalizada',
                                'suspension' => 'Suspendida',
                                'pendiente_coordinar' => 'Pendiente',
                                default => 'Sin Estado',
                            };
                        @endphp
                        <tr class="@if($instanciaData['has_suspension']) table-danger @endif @if($instanciaData['has_priority']) table-warning @endif" 
                        style="@if($instanciaData['has_suspension']) border-left: 4px solid #dc3545; @endif @if($instanciaData['has_priority']) border-left: 4px solid #ffc107; @endif"
                        data-order="{{ $numCoti }}">
                            <td>
                                <div class="fw-bold">#{{ $coti->coti_num }}
                                    @if($instanciaData['has_suspension'])
                                        <span class="badge bg-danger ms-2">Suspendida</span>
                                    @elseif($instanciaData['has_priority'])
                                        <span class="badge bg-warning text-dark">
                                            <x-heroicon-o-star style="width: 12px; height: 12px;" class="me-1" />
                                            Prioritaria
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">{{ $coti->coti_descripcion ?? 'N/A' }}</small>
                                    {{-- <span class="badge {{ $badgeColorEstado }}" style="font-size: 0.7em;">{{ $estadoTexto }}</span> --}}
                                </div>
                            </td>
                                <td>
                                    <div>{{ $coti->coti_empresa ?? 'N/A' }}</div>
                                    @if($coti->coti_establecimiento)
                                        <small class="text-muted">{{ $coti->coti_establecimiento }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($total > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <!-- Segmento de analizadas (verde) -->
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: {{ $porcentajes['analizadas'] }}%" 
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-placement="bottom"
                                                     title="Analizadas: {{ round($porcentajes['analizadas']) }}%">
                                                </div>
                                                
                                                <!-- Segmento en proceso (azul) -->
                                                <div class="progress-bar bg-info" 
                                                     role="progressbar" 
                                                     style="width: {{ $porcentajes['en_proceso'] }}%" 
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-placement="bottom"
                                                     title="En proceso: {{ round($porcentajes['en_proceso']) }}%">
                                                </div>
                                                
                                                <!-- Segmento coordinadas (amarillo) -->
                                                <div class="progress-bar bg-warning" 
                                                     role="progressbar" 
                                                     style="width: {{ $porcentajes['coordinadas'] }}%" 
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-placement="bottom"
                                                     title="Coordinadas: {{ round($porcentajes['coordinadas']) }}%">
                                                </div>
                                            </div>
                                            <small class="text-nowrap">
                                                {{ $analizadas + $enProceso + $coordinadas }}/{{ $total }}
                                            </small>
                                        </div>
                                        @if($porcentajes['total'] > 0 && $porcentajes['total'] < 100)
                                            <small class="d-block mt-1 @if($instanciaData['has_suspension']) text-danger fw-bold @else text-muted @endif">
                                                {{ round($porcentajes['total']) }}%
                                                @if($instanciaData['has_suspension'])
                                                    <x-heroicon-o-exclamation-triangle style="width: 16px; height: 16px;" class="ms-1" />
                                                @endif
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-dark">Sin análisis</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div>{{ $coti->coti_fechaaprobado ? \Carbon\Carbon::parse($coti->coti_fechaaprobado)->format('d/m/Y') : '-' }}</div>
                                    @if($coti->coti_fechafin)
                                        <small class="text-muted">Vence: {{ \Carbon\Carbon::parse($coti->coti_fechafin)->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>{{ $coti->matriz->matriz_descripcion ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ url('/ordenes/' . $numCoti) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="bottom"
                                           title="Gestionar orden">
                                           <x-heroicon-o-pencil style="width: 15px; height: 15px;" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Vista móvil -->
<div class="d-block d-lg-none">
    <div class="row g-3">
        @foreach($ordenes as $numCoti => $instanciaData)
            @php
                $coti = $instanciaData['instancias']->first()->cotizacion;
                
                // Calcular estados para la barra de progreso
                $analizadas = $instanciaData['instancias']->where('cotio_estado_analisis', 'analizado')->count();
                $enProceso = $instanciaData['instancias']->where('cotio_estado_analisis', 'en revision analisis')->count();
                $coordinadas = $instanciaData['instancias']->where('cotio_estado_analisis', 'coordinado analisis')->count();
                $total = $instanciaData['instancias']->count();
                
                $porcentajes = [
                    'analizadas' => $total > 0 ? ($analizadas / $total) * 100 : 0,
                    'en_proceso' => $total > 0 ? ($enProceso / $total) * 100 : 0,
                    'coordinadas' => $total > 0 ? ($coordinadas / $total) * 100 : 0,
                    'total' => $total > 0 ? (($analizadas + $enProceso + $coordinadas) / $total) * 100 : 0
                ];

                // Estado predominante para vista móvil
                $estadoPredominante = $instanciaData['estado_predominante'] ?? 'pendiente_coordinar';
                $badgeColorEstado = match($estadoPredominante) {
                    'coordinado analisis' => 'bg-warning text-dark',
                    'en revision analisis' => 'bg-info text-white',
                    'analizado' => 'bg-success text-white',
                    'suspension' => 'bg-danger text-white',
                    'pendiente_coordinar' => 'bg-secondary text-white',
                    default => 'bg-secondary text-white',
                };
                $estadoTexto = match($estadoPredominante) {
                    'coordinado analisis' => 'Coordinada',
                    'en revision analisis' => 'En Revisión',
                    'analizado' => 'Finalizada',
                    'suspension' => 'Suspendida',
                    'pendiente_coordinar' => 'Pendiente',
                    default => 'Sin Estado',
                };
            @endphp
            <div class="col-12">
                <div class="card shadow-sm border-start border-4 
                @if($instanciaData['has_suspension']) border-danger
                @elseif($instanciaData['has_priority']) border-warning
                @elseif($coti->coti_estado == 'A') border-success
                @elseif($coti->coti_estado == 'E') border-warning
                @elseif($coti->coti_estado == 'S') border-danger
                @else border-secondary @endif">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-1">#{{ $numCoti }}</h5>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if($instanciaData['has_suspension'])
                                        <span class="badge bg-danger">Suspendida</span>
                                    @elseif($instanciaData['has_priority'])
                                        <span class="badge bg-warning text-dark">
                                            <x-heroicon-o-star style="width: 12px; height: 12px;" class="me-1" />
                                            Prioritaria
                                        </span>
                                    @endif
                                    <span class="badge {{ $badgeColorEstado }}">{{ $estadoTexto }}</span>
                                    <span class="badge 
                                        @if($coti->coti_estado == 'A') bg-success
                                        @elseif($coti->coti_estado == 'E') bg-warning
                                        @elseif($coti->coti_estado == 'S') bg-danger
                                        @else bg-secondary @endif">
                                        {{ trim($coti->coti_estado) }}
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $coti->coti_fechaaprobado ? \Carbon\Carbon::parse($coti->coti_fechaaprobado)->format('d/m/Y') : 'Pendiente' }}
                            </small>
                        </div>
                        
                        <h6 class="card-subtitle mb-2 text-muted">{{ $coti->coti_empresa }}</h6>
                        
                        @if($coti->coti_establecimiento)
                            <p class="small mb-1"><i class="fas fa-building me-1"></i> {{ $coti->coti_establecimiento }}</p>
                        @endif
                        
                        <div class="my-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Progreso de análisis</span>
                                <span class="small fw-bold" @if($instanciaData['has_suspension']) text-danger @endif>{{ round($porcentajes['total']) }}%</span>
                                @if($instanciaData['has_suspension'])
                                    <x-heroicon-o-exclamation-triangle style="width: 16px; height: 16px;" class="ms-1" />
                                @endif
                            </div>
                            <div class="progress" style="height: 8px;">
                                @if($instanciaData['has_suspension']) 
                                    <div class="progress-bar bg-danger" 
                                         style="width: {{ $porcentajes['total'] }}%">
                                    </div>
                                @endif
                                <!-- Segmento de analizadas (verde) -->
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $porcentajes['analizadas'] }}%">
                                </div>
                                
                                <!-- Segmento en proceso (azul) -->
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $porcentajes['en_proceso'] }}%">
                                </div>
                                
                                <!-- Segmento coordinadas (amarillo) -->
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $porcentajes['coordinadas'] }}%">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ $total }} análisis</small>
                                <small class="text-muted">{{ $analizadas + $enProceso + $coordinadas }} completados</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small text-muted me-2">
                                    <i class="fas fa-flask me-1"></i> {{ $coti->matriz->matriz_descripcion ?? 'N/A' }}
                                </span>
                                @if($coti->coti_fechafin)
                                    <span class="small text-muted">
                                        <i class="far fa-clock me-1"></i> Vence: {{ \Carbon\Carbon::parse($coti->coti_fechafin)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ url('/ordenes/' . $numCoti) }}" class="btn btn-sm btn-outline-primary">
                                    <x-heroicon-o-pencil style="width: 15px; height: 15px;" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $pagination->links() }}
</div>

<style>
    .progress {
        background-color: #f0f3f5;
    }
    .progress-bar + .progress-bar {
        border-left: 1px solid rgba(255,255,255,0.3);
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }

    .table-warning {
    background-color: #fff3cd;
    }
    .table-warning:hover {
        background-color: #ffeeba !important;
    }
</style>

<script>
    // Inicializar tooltips para los segmentos de progreso
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('.progress-bar[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                container: 'body',
                placement: 'top'
            });
        });
    });
</script>