@extends('layouts.app')

@section('title', 'Dashboard de Calibración - Inventario de Laboratorio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tools text-warning"></i>
                    Dashboard de Calibración
                </h1>
                <div>
                    <button id="btnEjecutarVerificacion" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Ejecutar Verificación
                    </button>
                    <a href="{{ route('inventarios.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Ver Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['total_equipos'] }}</h4>
                            <p class="card-text">Total Equipos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tools fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['equipos_con_calibracion'] }}</h4>
                            <p class="card-text">Con Calibración</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['proximos_24h'] }}</h4>
                            <p class="card-text">Próximos 24h</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['vencidos'] }}</h4>
                            <p class="card-text">Vencidos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Equipos Próximos a Calibración -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock text-warning"></i>
                        Equipos Próximos a Calibración (7 días)
                    </h5>
                </div>
                <div class="card-body">
                    @if($equiposProximos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Serie</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equiposProximos as $equipo)
                                        @php
                                            $fechaCalibracion = \Carbon\Carbon::parse($equipo->fecha_calibracion);
                                            $horasRestantes = \Carbon\Carbon::now()->diffInHours($fechaCalibracion, false);
                                            $diasRestantes = \Carbon\Carbon::now()->diffInDays($fechaCalibracion, false);
                                            $urgente = $horasRestantes <= 24;
                                        @endphp
                                        <tr class="{{ $urgente ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $equipo->equipamiento }}</strong><br>
                                                <small class="text-muted">{{ $equipo->marca_modelo }}</small>
                                            </td>
                                            <td>{{ $equipo->n_serie_lote }}</td>
                                            <td>
                                                {{ $fechaCalibracion->format('d/m/Y H:i') }}<br>
                                                <small class="text-{{ $urgente ? 'danger' : 'warning' }}">
                                                    {{ $horasRestantes < 24 ? $horasRestantes . ' horas' : $diasRestantes . ' días' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $equipo->estado === 'libre' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($equipo->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p>No hay equipos próximos a calibración</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Equipos con Calibración Vencida -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Equipos con Calibración Vencida
                    </h5>
                </div>
                <div class="card-body">
                    @if($equiposVencidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Serie</th>
                                        <th>Fecha Vencida</th>
                                        <th>Días Vencida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equiposVencidos as $equipo)
                                        @php
                                            $fechaCalibracion = \Carbon\Carbon::parse($equipo->fecha_calibracion);
                                            $diasVencida = \Carbon\Carbon::now()->diffInDays($equipo->fecha_calibracion);
                                        @endphp
                                        <tr class="table-danger">
                                            <td>
                                                <strong>{{ $equipo->equipamiento }}</strong><br>
                                                <small class="text-muted">{{ $equipo->marca_modelo }}</small>
                                            </td>
                                            <td>{{ $equipo->n_serie_lote }}</td>
                                            <td>{{ $fechaCalibracion->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $diasVencida }} días</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p>No hay equipos con calibración vencida</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Notificaciones Recientes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell text-info"></i>
                        Notificaciones Recientes de Calibración
                    </h5>
                </div>
                <div class="card-body">
                    @if($notificaciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Coordinador</th>
                                        <th>Mensaje</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notificaciones as $notificacion)
                                        <tr>
                                            <td>{{ $notificacion->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $notificacion->coordinador->usu_descripcion ?? 'N/A' }}</td>
                                            <td>{{ $notificacion->mensaje }}</td>
                                            <td>
                                                <span class="badge bg-{{ $notificacion->leida ? 'secondary' : 'primary' }}">
                                                    {{ $notificacion->leida ? 'Leída' : 'Nueva' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                            <p>No hay notificaciones recientes de calibración</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultados -->
<div class="modal fade" id="modalResultados" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultados de la Verificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalResultadosBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnEjecutarVerificacion = document.getElementById('btnEjecutarVerificacion');
    const modalResultados = new bootstrap.Modal(document.getElementById('modalResultados'));
    const modalResultadosBody = document.getElementById('modalResultadosBody');

    btnEjecutarVerificacion.addEventListener('click', function() {
        btnEjecutarVerificacion.disabled = true;
        btnEjecutarVerificacion.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ejecutando...';

        fetch('{{ route("calibracion.ejecutar-verificacion") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalResultadosBody.innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Verificación ejecutada exitosamente</h6>
                        <ul class="mb-0">
                            <li>Equipos encontrados: ${data.data.equipos_encontrados}</li>
                            <li>Notificaciones creadas: ${data.data.notificaciones_creadas}</li>
                            <li>Coordinadores notificados: ${data.data.coordinadores_notificados}</li>
                        </ul>
                    </div>
                `;
            } else {
                modalResultadosBody.innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Error</h6>
                        <p class="mb-0">${data.message}</p>
                    </div>
                `;
            }
            modalResultados.show();
        })
        .catch(error => {
            modalResultadosBody.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Error de conexión</h6>
                    <p class="mb-0">No se pudo ejecutar la verificación. Intente nuevamente.</p>
                </div>
            `;
            modalResultados.show();
        })
        .finally(() => {
            btnEjecutarVerificacion.disabled = false;
            btnEjecutarVerificacion.innerHTML = '<i class="fas fa-sync-alt"></i> Ejecutar Verificación';
        });
    });
});
</script>
@endpush 