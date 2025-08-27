@foreach($muestras as $coti)
@php
    $estado = trim($coti->coti_estado);
    $badgeClass = match ($estado) {
        'A' => 'bg-success',
        'E' => 'bg-warning',
        'S' => 'bg-danger',
        default => 'bg-secondary'
    };
    $estadoText = match ($estado) {
        'A' => 'Aprobado',
        'E' => 'En espera',
        'S' => 'Rechazado',
        default => $estado
    };
@endphp
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-md-row justify-content-between align-items-center align-items-md-center">
                <button class="btn btn-link text-decoration-none p-0 text-start d-flex align-items-center" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#tabla-{{ $coti->coti_num }}" 
                        aria-expanded="false" 
                        aria-controls="tabla-{{ $coti->coti_num }}"
                        onclick="toggleChevron('chevron-{{ $coti->coti_num }}')"
                        >
                    <h4 class="mb-0 text-primary me-2">
                        Cotización Nº {{ $coti->coti_num }}
                        {{-- <span class="badge {{ $badgeClass }} ms-2">{{ $estadoText }}</span> --}}
                    </h4>
                    <x-heroicon-o-chevron-up id="chevron-{{ $coti->coti_num }}" class="text-primary chevron-icon" style="width: 20px; height: 20px;" />
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

        <div id="tabla-{{ $coti->coti_num }}" class="collapse">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Detalles de la cotización</h5>
                    <a class="btn btn-sm btn-primary" href="{{ url('/show/'.$coti->coti_num) }}">
                        Asignar muestras
                    </a>
                </div>
                
                <!-- Aquí puedes agregar más detalles específicos de la cotización -->
                <div class="mb-3">
                    <strong>Observaciones:</strong>
                    <p>{{ $coti->coti_observaciones ?: 'Sin observaciones' }}</p>
                </div>
                
                <!-- Si tienes items de cotización, podrías mostrarlos aquí -->
                @if($coti->items && $coti->items->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Producto/Servicio</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coti->items as $item)
                                    <tr>
                                        <td>{{ $item->descripcion }}</td>
                                        <td>{{ $item->cantidad }}</td>
                                        <td>${{ number_format($item->precio_unitario, 2) }}</td>
                                        <td>${{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-primary fw-bold">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td>${{ number_format($coti->coti_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        No hay items registrados para esta cotización.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach

<div class="d-flex justify-content-center mt-4">
    {{ $muestras->links() }}
</div>

<style>
    .chevron-icon {
        transition: transform 0.3s ease;
    }
    .chevron-icon.rotated {
        transform: rotate(180deg);
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