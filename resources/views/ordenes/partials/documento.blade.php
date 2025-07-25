@foreach($ordenes as $numCoti => $data)
    @php
        $instancias = collect($data['instancias']); // Convert to Collection
        $coti = $instancias->first()->cotizacion ?? null;
    @endphp
    
    @if($coti)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-md-row justify-content-between align-items-center align-items-md-center">
                <button class="btn btn-link text-decoration-none p-0 text-start d-flex align-items-center" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#tabla-{{ $numCoti }}" 
                        aria-expanded="false" 
                        aria-controls="tabla-{{ $numCoti }}"
                        onclick="toggleChevron('chevron-{{ $numCoti }}')">
                    <h4 class="mb-0 text-primary me-2">
                        Orden Nº {{ $numCoti }}
                        {{-- <span class="badge ms-2 bg-{{ $coti->coti_estado == 'A' ? 'success' : ($coti->coti_estado == 'E' ? 'warning' : 'danger') }}">
                            {{ $coti->coti_estado }}
                        </span> --}}
                    </h4>
                    <x-heroicon-o-chevron-up id="chevron-{{ $numCoti }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
                </button>
                <a class="btn btn-outline-primary mt-md-0"
                   href="https://www.google.com/maps/search/?api=1&query={{ $coti->coti_direccioncli }}, {{ $coti->coti_localidad }}, {{ $coti->coti_partido }}">
                    <span class="d-none d-md-inline">Ver en Maps</span>
                    <x-heroicon-o-map class="d-md-none" style="width: 18px; height: 18px;" />
                </a>
            </div>

            <div class="mt-2 small">
                <div><strong>Fecha Alta:</strong> {{ $coti->coti_fechaalta }}</div>
                <div><strong>Fecha Aprobación:</strong> {{ $coti->coti_fechaaprobado ?: 'Pendiente' }}</div>
                <div><strong>Dirección:</strong> {{ $coti->coti_direccioncli }}, {{ $coti->coti_localidad }}</div>
                <div><strong>Cliente:</strong> {{ $coti->coti_empresa }} - {{ $coti->coti_establecimiento }}</div>
                <div><strong>Responsable:</strong> {{ $coti->responsable->usu_descripcion ?? 'Sin asignar' }}</div>
            </div>
        </div>

        <div id="tabla-{{ $numCoti }}" class="collapse">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Muestras/Análisis</h5>
                    <a class="btn btn-sm btn-primary" href="{{ route('ordenes.ver-detalle', $numCoti) }}">
                        Gestionar muestras
                    </a>
                </div>
                
                <!-- Listado de instancias/muestras -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Muestreo</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instancias as $instancia)
                                @if($instancia->cotio_subitem == 0)
                                @php
                                    $estadoMuestra = strtolower($instancia->cotio_estado_analisis ?? 'pendiente');
                                    $badgeClassMuestra = match ($estadoMuestra) {
                                        'coordinado analisis' => 'warning',
                                        'en revision analisis' => 'info',
                                        'analizado' => 'success',
                                        'suspension' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                    <tr>
                                        <td>{{ $instancia->cotio_descripcion }}</td>
                                        <td>
                                            <span class="badge bg-{{ $badgeClassMuestra }}">
                                                {{ $instancia->cotio_estado_analisis }}
                                            </span>
                                        </td>
                                        <td>{{ $instancia->fecha_muestreo ?: 'No programado' }}</td>
                                        <td>{{ $instancia->responsable_muestreo ?? 'Sin asignar' }}</td>
                                        <td>
                                            @if($instancia->cotio_item !== null && $instancia->instance_number !== null)
                                                <a href="{{ route('categoria.verOrden', [
                                                    'cotizacion' => $numCoti,
                                                    'item' => $instancia->cotio_item,
                                                    'instance' => $instancia->instance_number
                                                ]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                    Detalles
                                                </a>
                                            @else
                                                <span class="text-muted">Detalles no disponibles</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Observaciones generales -->
                <div class="mt-3">
                    <strong>Observaciones:</strong>
                    <p>{{ $coti->coti_observaciones ?: 'Sin observaciones' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<div class="d-flex justify-content-center mt-4">
    {{ $pagination->links() }}
</div>

<style>
    .chevron-icon {
        transition: transform 0.3s ease;
    }
    .chevron-icon.rotated {
        transform: rotate(180deg);
    }
    .badge {
        font-size: 0.8em;
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