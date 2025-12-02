@extends('layouts.app')

@section('title', 'Historial de Cambios de Precios')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Historial de Cambios de Precios</h1>
        <div>
            <a href="{{ route('items.cambios-masivos-precios') }}" class="btn btn-primary">Nuevo Cambio Masivo</a>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" style="display:none"></div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('items.historial-precios') }}" class="row g-2">
                <div class="col-md-4">
                    <label for="operacion_id" class="form-label">Filtrar por Operación</label>
                    <select name="operacion_id" id="operacion_id" class="form-select">
                        <option value="">Todas las operaciones</option>
                        @foreach($operaciones as $operacion)
                            <option value="{{ $operacion->operacion_id }}" {{ request('operacion_id') == $operacion->operacion_id ? 'selected' : '' }}>
                                {{ substr($operacion->operacion_id, 0, 8) }}... - {{ $operacion->cantidad }} cambios - {{ ($operacion->fecha instanceof \Carbon\Carbon) ? $operacion->fecha->format('d/m/Y H:i') : \Carbon\Carbon::parse($operacion->fecha)->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="solo_activos" id="solo_activos" value="1" {{ request('solo_activos') ? 'checked' : '' }}>
                        <label class="form-check-label" for="solo_activos">
                            Solo cambios activos (no revertidos)
                        </label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
                    <a href="{{ route('items.historial-precios') }}" class="btn btn-outline-secondary ms-2">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Fecha</th>
                            <th>Ítem</th>
                            <th style="width: 120px;">Precio Anterior</th>
                            <th style="width: 120px;">Precio Nuevo</th>
                            <th style="width: 100px;">Tipo</th>
                            <th style="width: 100px;">Valor Aplicado</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th style="width: 100px;">Estado</th>
                            <th style="width: 120px;">Operación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $cambio)
                            <tr>
                                <td class="align-middle">
                                    @php
                                        $fechaCambio = $cambio->fecha_cambio instanceof \Carbon\Carbon 
                                            ? $cambio->fecha_cambio 
                                            : \Carbon\Carbon::parse($cambio->fecha_cambio);
                                    @endphp
                                    <small>{{ $fechaCambio->format('d/m/Y') }}<br>{{ $fechaCambio->format('H:i') }}</small>
                                </td>
                                <td class="align-middle">
                                    <strong>{{ $cambio->item->cotio_descripcion }}</strong><br>
                                    <small class="text-muted">ID: {{ $cambio->item_id }}</small>
                                </td>
                                <td class="align-middle">${{ number_format($cambio->precio_anterior, 2, ',', '.') }}</td>
                                <td class="align-middle">
                                    <strong>${{ number_format($cambio->precio_nuevo, 2, ',', '.') }}</strong>
                                </td>
                                <td class="align-middle">
                                    @if($cambio->tipo_cambio === 'porcentaje')
                                        <span class="badge bg-info">%</span>
                                    @else
                                        <span class="badge bg-secondary">Fijo</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($cambio->tipo_cambio === 'porcentaje')
                                        {{ $cambio->valor_aplicado > 0 ? '+' : '' }}{{ number_format($cambio->valor_aplicado, 2) }}%
                                    @else
                                        ${{ $cambio->valor_aplicado > 0 ? '+' : '' }}{{ number_format($cambio->valor_aplicado, 2) }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <small>{{ $cambio->descripcion ?? '-' }}</small>
                                </td>
                                <td class="align-middle">
                                    <small>{{ $cambio->usuario->usu_descripcion ?? 'N/A' }}</small>
                                </td>
                                <td class="align-middle">
                                    @if($cambio->revertido)
                                        <span class="badge bg-danger">Revertido</span>
                                        @if($cambio->fecha_reversion)
                                            <br><small class="text-muted">{{ ($cambio->fecha_reversion instanceof \Carbon\Carbon) ? $cambio->fecha_reversion->format('d/m/Y H:i') : \Carbon\Carbon::parse($cambio->fecha_reversion)->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-success">Activo</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('items.historial-precios', ['operacion_id' => $cambio->operacion_id]) }}" class="text-decoration-none">
                                        <code class="small">{{ substr($cambio->operacion_id, 0, 8) }}...</code>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No hay cambios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($historial->hasPages())
            <div class="card-footer">{{ $historial->links() }}</div>
        @endif
    </div>

    @if($operaciones->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Operaciones Masivas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID Operación</th>
                                <th>Fecha</th>
                                <th>Cantidad de Cambios</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operaciones as $operacion)
                                @php
                                    $cambiosOperacion = \App\Models\CotioItemPrecioHistorial::porOperacion($operacion->operacion_id)->get();
                                    $todosRevertidos = $cambiosOperacion->every(fn($c) => $c->revertido);
                                    $algunosRevertidos = $cambiosOperacion->some(fn($c) => $c->revertido) && !$todosRevertidos;
                                @endphp
                                <tr>
                                    <td><code>{{ substr($operacion->operacion_id, 0, 8) }}...</code></td>
                                    <td>{{ ($operacion->fecha instanceof \Carbon\Carbon) ? $operacion->fecha->format('d/m/Y H:i') : \Carbon\Carbon::parse($operacion->fecha)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $operacion->cantidad }} cambios</td>
                                    <td>
                                        @if($todosRevertidos)
                                            <span class="badge bg-danger">Revertida</span>
                                        @elseif($algunosRevertidos)
                                            <span class="badge bg-warning">Parcialmente revertida</span>
                                        @else
                                            <span class="badge bg-success">Activa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('items.historial-precios', ['operacion_id' => $operacion->operacion_id]) }}" class="btn btn-sm btn-outline-info">
                                            Ver Detalles
                                        </a>
                                        @if(!$todosRevertidos)
                                            <form action="{{ route('items.revertir-cambios', $operacion->operacion_id) }}" method="POST" class="d-inline js-revertir-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Revertir
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-success');
        if (flash && flash.dataset.message) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: flash.dataset.message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        document.querySelectorAll('.js-revertir-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Revertir esta operación?',
                    text: 'Se restaurarán todos los precios a sus valores anteriores. Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, revertir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
    </script>
</div>
@endsection

