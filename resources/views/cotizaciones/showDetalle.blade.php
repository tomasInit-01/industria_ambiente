@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                @php
                    $tareas = $cotizacion->tareas ?? collect();
                    $tareasCollection = $tareas instanceof \Illuminate\Support\Collection ? $tareas : collect($tareas);
                    $ensayos = $tareasCollection->where('cotio_subitem', 0);
                    $componentes = $tareasCollection->where('cotio_subitem', '>', 0);

                    $detallesFinancieros = $ensayos->map(function ($ensayo) use ($componentes) {
                        $cantidadMuestras = (float) ($ensayo->cotio_cantidad ?? 1);
                        if ($cantidadMuestras <= 0) {
                            $cantidadMuestras = 1;
                        }

                        $componentesDelEnsayo = $componentes->where('cotio_item', $ensayo->cotio_item);

                        $precioUnitario = $componentesDelEnsayo->sum(function ($componente) {
                            $precio = (float) ($componente->cotio_precio ?? 0);
                            $cantidad = (float) ($componente->cotio_cantidad ?? 1);
                            if ($cantidad <= 0) {
                                $cantidad = 1;
                            }
                            return $precio * $cantidad;
                        });

                        return [
                            'item' => $ensayo->cotio_item,
                            'descripcion' => $ensayo->cotio_descripcion,
                            'cantidad' => $cantidadMuestras,
                            'precio_unitario' => $precioUnitario,
                            'subtotal' => $cantidadMuestras * $precioUnitario,
                        ];
                    });

                    $componentesSinCategoria = $componentes->filter(function ($componente) use ($ensayos) {
                        return !$ensayos->contains('cotio_item', $componente->cotio_item);
                    });

                    $totalComponentesSinCategoria = $componentesSinCategoria->sum(function ($componente) {
                        $precio = (float) ($componente->cotio_precio ?? 0);
                        $cantidad = (float) ($componente->cotio_cantidad ?? 1);
                        if ($cantidad <= 0) {
                            $cantidad = 1;
                        }
                        return $precio * $cantidad;
                    });

                    $totalCalculado = (float) $detallesFinancieros->sum('subtotal') + (float) $totalComponentesSinCategoria;

                    $formatCurrency = function ($value) {
                        return '$' . number_format((float) $value, 2, ',', '.');
                    };

                    $formatDate = function ($date) {
                        return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '—';
                    };

                    $formatValue = function ($value, $type = null) use ($formatCurrency, $formatDate) {
                        if ($type === 'currency') {
                            return $formatCurrency($value ?? 0);
                        }

                        if ($type === 'date') {
                            return $formatDate($value);
                        }

                        if (is_null($value) || $value === '') {
                            return '—';
                        }

                        return $value;
                    };

                    $totalMuestras = $ensayos->sum(function ($ensayo) {
                        $cantidad = (float) ($ensayo->cotio_cantidad ?? 1);
                        return $cantidad > 0 ? $cantidad : 1;
                    });

                    $estadoTexto = trim((string) ($cotizacion->coti_estado ?? ''));
                    $estadoClass = match (strtolower($estadoTexto)) {
                        'aprobada', 'aprobado', 'a' => 'bg-success',
                        'pendiente', 'p' => 'bg-warning text-dark',
                        'en proceso', 'en curso', 'en ejecución', 'en ejecucion' => 'bg-info text-dark',
                        'rechazada', 'cancelada', 'c' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $cliente = $cotizacion->cliente ?? null;
                    $descuentoGlobal = max((float) ($descuentoGlobalCliente ?? 0), 0);
                    $descuentoSector = max((float) ($descuentoSectorCliente ?? 0), 0);
                    $descuentoTotal = max((float) ($descuentoTotalCliente ?? ($descuentoGlobal + $descuentoSector)), 0);
                    $descuentoGlobalMonto = $totalCalculado * ($descuentoGlobal / 100);
                    $descuentoSectorMonto = $totalCalculado * ($descuentoSector / 100);
                    $importeConDescuento = $totalCalculado - ($descuentoGlobalMonto + $descuentoSectorMonto);
                    $sectorEtiquetaMostrar = $sectorEtiqueta ?? ($cotizacion->coti_sector ?? optional($cliente)->cli_codigocrub);

                    $formatPercent = function ($value) {
                        return number_format((float) $value, 2, ',', '.') . '%';
                    };
                @endphp

                <div class="card-header bg-primary text-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h5 class="mb-1">Detalles de la Cotización</h5>
                            <small class="text-white-50">Información completa de la cotización #{{ $cotizacion->coti_num }}</small>
                        </div>
                        <a href="{{ route('ventas.print', $cotizacion->coti_num) }}"
                            class="btn btn-sm btn-outline-secondary"
                            target="_blank"
                            style="border-color: white;"
                            rel="noopener"
                            title="Imprimir cotización">
                             <x-heroicon-o-printer style="width: 18px; height: 18px; color: white;" />
                         </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="resumen-highlight mb-4">
                        <div class="resumen-item">
                            <span>Importe sin descuento</span>
                            <h3 class="mb-0">{{ $formatCurrency($totalCalculado) }}</h3>
                        </div>
                        <div class="resumen-item">
                            <span>Descuento global cliente</span>
                        <h3 class="mb-0">{{ $descuentoGlobal > 0 ? $formatPercent($descuentoGlobal) : 'Sin descuento' }}</h3>
                            @if($descuentoGlobal > 0)
                            <small class="text-muted">Ajuste: -{{ $formatCurrency($descuentoGlobalMonto) }}</small>
                            @endif
                        </div>
                    <div class="resumen-item">
                        <span>Descuento sector {{ $sectorEtiquetaMostrar ? '(' . $sectorEtiquetaMostrar . ')' : '' }}</span>
                        <h3 class="mb-0">{{ $descuentoSector > 0 ? $formatPercent($descuentoSector) : 'Sin descuento' }}</h3>
                        @if($descuentoSector > 0)
                            <small class="text-muted">Ajuste: -{{ $formatCurrency($descuentoSectorMonto) }}</small>
                        @endif
                    </div>
                        <div class="resumen-item">
                            <span>Importe final</span>
                            <h3 class="mb-0">{{ $formatCurrency($importeConDescuento) }}</h3>
                        <small class="text-muted">
                            Descuento total: {{ $descuentoTotal > 0 ? $formatPercent($descuentoTotal) : 'Sin descuento' }}
                        </small>
                        </div>
                        <div class="resumen-item">
                            <span>Total de muestras</span>
                            <h3 class="mb-0">{{ number_format($totalMuestras, 0, ',', '.') }}</h3>
                            <small class="text-muted">Cantidad planificada</small>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-4 col-md-6">
                            <div class="info-section">
                                <h6 class="section-title">Información General</h6>
                                <div class="info-grid">
                                    <div>
                                        <span class="info-label">Número</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_num) }}</span>
                                    </div>
                                    @if(!empty($cotizacion->coti_para))
                                    <div>
                                        <span class="info-label">Para</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_para) }}</span>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="info-label">Descripción</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_descripcion) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Código Cliente</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_codigocli) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Código Matriz</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_codigomatriz) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Matriz</span>
                                        <span class="info-value">{{ optional($cotizacion->matriz)->matriz_descripcion ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Estado</span>
                                        <span class="badge estado-pill {{ $estadoClass }}">
                                            {{ $cotizacion->coti_estado ?? 'Sin estado' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="info-label">Aprobado por</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_aprobo) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Responsable</span>
                                        <span class="info-value">
                                            {{ optional($cotizacion->responsable)->usu_descripcion ?? $formatValue($cotizacion->coti_responsable) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="info-label">Sucursal</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_codigosuc) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="info-section">
                                <h6 class="section-title">Fechas Clave</h6>
                                <div class="info-grid">
                                    <div>
                                        <span class="info-label">Fecha Alta</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_fechaalta, 'date') }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Fecha Alta Técnica</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_fechaaltatecnica, 'date') }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Fecha En Curso</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_fechaencurso, 'date') }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Fecha Aprobación</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_fechaaprobado, 'date') }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Fecha Fin</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_fechafin, 'date') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="info-section">
                                <h6 class="section-title">Cliente y Establecimiento</h6>
                                <div class="info-grid">
                                    <div>
                                        <span class="info-label">Empresa</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_empresa) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Establecimiento</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_establecimiento) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">CUIT</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_cuit) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Contacto</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_contacto) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Descuento global</span>
                                        <span class="info-value">{{ $descuentoGlobal > 0 ? $formatPercent($descuentoGlobal) : 'Sin descuento' }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Descuento sector</span>
                                        <span class="info-value">
                                            {{ $descuentoSector > 0 ? $formatPercent($descuentoSector) : 'Sin descuento' }}
                                            @if($descuentoSector > 0 && $sectorEtiquetaMostrar)
                                                <small class="d-block text-muted">{{ $sectorEtiquetaMostrar }}</small>
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="info-label">Descuento total</span>
                                        <span class="info-value">{{ $descuentoTotal > 0 ? $formatPercent($descuentoTotal) : 'Sin descuento' }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Correo</span>
                                        <span class="info-value">
                                            @if(!empty($cotizacion->coti_mail1))
                                                <a href="mailto:{{ $cotizacion->coti_mail1 }}" class="link-primary text-decoration-none">
                                                    {{ $cotizacion->coti_mail1 }}
                                                </a>
                                            @else
                                                {{ $formatValue(null) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="info-label">Teléfono</span>
                                        <span class="info-value">
                                            @if(!empty($cotizacion->coti_telefono))
                                                <a href="tel:{{ preg_replace('/\s+/', '', $cotizacion->coti_telefono) }}" class="link-primary text-decoration-none">
                                                    {{ $cotizacion->coti_telefono }}
                                                </a>
                                            @else
                                                {{ $formatValue(null) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="info-label">Sector</span>
                                        <span class="info-value">{{ $formatValue($sectorEtiquetaMostrar ?? ($cotizacion->coti_sector ?? optional($cotizacion->cliente)->cli_codigocrub)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6">
                            <div class="info-section">
                                <h6 class="section-title">Ubicación</h6>
                                <div class="info-grid">
                                    <div>
                                        <span class="info-label">Dirección</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_direccioncli) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Localidad</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_localidad) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Partido</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_partido) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Código Postal</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_codigopostal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6">
                            <div class="info-section">
                                <h6 class="section-title">Referencias y Contratos</h6>
                                <div class="info-grid">
                                    <div>
                                        <span class="info-label">Referencia Tipo</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_referencia_tipo) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Referencia Valor</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_referencia_valor) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">OC Referencia</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_oc_referencia) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">HES/HAS Tipo</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_hes_has_tipo) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">HES/HAS Valor</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_hes_has_valor) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">GR/Contrato Tipo</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_gr_contrato_tipo) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">GR/Contrato</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_gr_contrato) }}</span>
                                    </div>
                                    <div>
                                        <span class="info-label">Otra Referencia</span>
                                        <span class="info-value">{{ $formatValue($cotizacion->coti_otro_referencia) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($cotizacion->coti_notas))
                        <div class="alert alert-info mt-4 mb-0">
                            <h6 class="mb-2">Notas / Observaciones</h6>
                            <p class="mb-0">{{ $cotizacion->coti_notas }}</p>
                        </div>
                    @endif
                </div>

                @if($detallesFinancieros->isNotEmpty() || $componentesSinCategoria->isNotEmpty())
                    <div class="card-body border-top bg-light-subtle">
                        <h6 class="section-title mb-3">Resumen Económico</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Item</th>
                                        <th class="text-start">Descripción</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unitario</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($detallesFinancieros as $detalle)
                                        <tr>
                                            <td class="text-start">#{{ $detalle['item'] }}</td>
                                            <td class="text-start">{{ $detalle['descripcion'] ?? 'Sin descripción' }}</td>
                                            <td class="text-center">{{ number_format($detalle['cantidad'], 2, ',', '.') }}</td>
                                            <td class="text-end">{{ $formatCurrency($detalle['precio_unitario']) }}</td>
                                            <td class="text-end">{{ $formatCurrency($detalle['subtotal']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay ensayos asociados para calcular importes.</td>
                                        </tr>
                                    @endforelse

                                    @if($componentesSinCategoria->isNotEmpty())
                                        <tr class="table-secondary">
                                            <td colspan="3" class="text-start fw-bold">Componentes sin categoría asociada</td>
                                            <td colspan="2" class="text-end fw-bold">{{ $formatCurrency($totalComponentesSinCategoria) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Importe Total Calculado</th>
                                        <th class="text-end">{{ $formatCurrency($totalCalculado) }}</th>
                                    </tr>
                                    @if($descuentoGlobal > 0)
                                        <tr>
                                            <th colspan="4" class="text-end text-danger">
                                                Descuento global ({{ $formatPercent($descuentoGlobal) }})
                                            </th>
                                            <th class="text-end text-danger">-{{ $formatCurrency($descuentoGlobalMonto) }}</th>
                                        </tr>
                                    @endif
                                    @if($descuentoSector > 0)
                                        <tr>
                                            <th colspan="4" class="text-end text-danger">
                                                Descuento sector
                                                @if($sectorEtiquetaMostrar)
                                                    ({{ $sectorEtiquetaMostrar }})
                                                @endif
                                                ({{ $formatPercent($descuentoSector) }})
                                            </th>
                                            <th class="text-end text-danger">-{{ $formatCurrency($descuentoSectorMonto) }}</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="4" class="text-end">Importe Total con descuento</th>
                                        <th class="text-end">{{ $formatCurrency($importeConDescuento) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
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

    .resumen-highlight {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 0.75rem;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .resumen-item span {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }

    .resumen-item h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #212529;
    }

    .info-section {
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        background-color: #ffffff;
        height: 100%;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .section-title {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
    }

    .info-grid {
        display: grid;
        gap: 0.75rem;
    }

    .info-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.08em;
        margin-bottom: 0.1rem;
    }

    .info-value {
        font-weight: 600;
        color: #212529;
        word-break: break-word;
    }

    .estado-pill {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.5rem 1rem;
        border-radius: 50rem;
    }

    .bg-light-subtle {
        background-color: #f8f9fc !important;
    }

    .table-sm thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6c757d;
    }
</style>
@endsection