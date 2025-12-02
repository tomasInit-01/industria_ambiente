@extends('layouts.app')

@section('content')
<div id="cotizacionLoadingOverlay" class="cotizacion-loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h4 mb-0">
                        Editar Cotización #{{ $cotizacion->coti_num }}
                        @if($cotizacion->coti_version != 1)
                            <small class="text-muted ms-2">.{{ $cotizacion->coti_version ?? 1 }}</small>
                        @endif
                    </h2>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                        <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" />
                        Volver
                    </a>
                </div>
            </div>

            <!-- Mensajes de éxito y error -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('ventas.update', $cotizacion->coti_num) }}" id="cotizacionForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Header con información básica -->
                        <div class="border-bottom px-4 py-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label for="cliente_codigo" class="form-label fw-semibold mb-1">Cliente:</label>
                                    <div class="position-relative" id="clienteBuscadorWrapper">
                                        <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" id="cliente_codigo" name="coti_codigocli" 
                                           value="{{ trim($cotizacion->coti_codigocli) }}" placeholder="Escribe nombre o código..." autocomplete="off" required>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="btnBuscarCliente">
                                                <x-heroicon-o-magnifying-glass style="width: 14px; height: 14px;" />
                                            </button>
                                        </div>
                                        <div class="dropdown-menu w-100 shadow-sm p-0" id="clienteResultados"></div>
                                    </div>
                                    <!-- Campos hidden para datos del cliente -->
                                    <input type="hidden" id="cliente_razon_social_hidden" name="cliente_razon_social" value="{{ $cotizacion->coti_empresa }}">
                                    <input type="hidden" id="cliente_direccion_hidden" name="cliente_direccion" value="{{ $cotizacion->coti_direccioncli }}">
                                    <input type="hidden" id="cliente_localidad_hidden" name="cliente_localidad" value="{{ $cotizacion->coti_localidad }}">
                                    <input type="hidden" id="cliente_cuit_hidden" name="cliente_cuit" value="{{ $cotizacion->coti_cuit }}">
                                    <input type="hidden" id="cliente_codigo_postal_hidden" name="cliente_codigo_postal" value="{{ $cotizacion->coti_codigopostal }}">
                                    <input type="hidden" id="cliente_telefono_hidden" name="cliente_telefono" value="{{ $cotizacion->coti_telefono }}">
                                    <input type="hidden" id="cliente_correo_hidden" value="{{ $cotizacion->coti_mail1 }}">
                                    <input type="hidden" id="cliente_sector_hidden" value="{{ trim($cotizacion->coti_sector ?? '') }}">
                                    <input type="hidden" id="cliente_descuento_hidden" value="{{ number_format($descuentoCliente ?? 0, 2, '.', '') }}" data-descuento-global="{{ number_format($descuentoGlobalCliente ?? 0, 2, '.', '') }}" data-descuento-sector="{{ number_format($descuentoSectorAplicado ?? 0, 2, '.', '') }}" data-sector-etiqueta="{{ $sectorEtiqueta ?? trim($cotizacion->coti_sector ?? '') }}">
                                    <input type="hidden" id="ensayos_data" name="ensayos_data">
                                    <input type="hidden" id="componentes_data" name="componentes_data">
                                </div>
                                <div class="col-md-4">
                                    <label for="cliente_nombre" class="form-label fw-semibold mb-1">&nbsp;</label>
                                    <input type="text" class="form-control form-control-sm" id="cliente_nombre" 
                                           value="{{ $cotizacion->coti_empresa }}" placeholder="Seleccione un cliente" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="sucursal" class="form-label fw-semibold mb-1">Sucursal:</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal" name="coti_codigosuc"
                                           value="{{ $cotizacion->coti_codigosuc }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="numero" class="form-label fw-semibold mb-1">Nro:</label>
                                    <input type="text" class="form-control form-control-sm" id="numero" name="coti_num" 
                                           value="{{ $cotizacion->coti_num }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="Para" class="form-label fw-semibold mb-1">Para:</label>
                                    <input type="text" class="form-control form-control-sm" id="coti_para" name="coti_para" 
                                           value="{{ old('coti_para', $cotizacion->coti_para) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Navegación de solapas -->
                        <ul class="nav nav-tabs nav-tabs-custom" id="cotizacionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                        data-bs-target="#general" type="button" role="tab">
                                    General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="gestion-tab" data-bs-toggle="tab" 
                                        data-bs-target="#gestion" type="button" role="tab">
                                    Gestión
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="empresa-tab" data-bs-toggle="tab" 
                                        data-bs-target="#empresa" type="button" role="tab">
                                    Empresa
                                </button>
                            </li>
                        </ul>

                        <!-- Contenido de las solapas -->
                        <div class="tab-content" id="cotizacionTabsContent">
                            <!-- Solapa General -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="p-4">
                                    <!-- Información superior -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label for="descripcion" class="form-label">Descripción:</label>
                                            <input type="text" class="form-control" id="descripcion" name="coti_descripcion" 
                                                   value="{{ old('coti_descripcion', $cotizacion->coti_descripcion) }}" placeholder="Descripción de la cotización...">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-primary w-100">Clonar</button>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="abierta" name="abierta" {{ old('abierta') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="abierta">Abierta</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_alta" class="form-label">Alta:</label>
                                            <input type="date" class="form-control" id="fecha_alta" name="coti_fechaalta" 
                                                   value="{{ old('coti_fechaalta', $cotizacion->coti_fechaalta ? $cotizacion->coti_fechaalta->format('Y-m-d') : date('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_venc" class="form-label">Venc:</label>
                                            <input type="date" class="form-control" id="fecha_venc" name="coti_fechafin"
                                                   value="{{ old('coti_fechafin', $cotizacion->coti_fechafin ? $cotizacion->coti_fechafin->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label for="usuario" class="form-label">Usuario:</label>
                                            @php
                                                $usuarioActual = auth()->user();
                                                $usuarioTexto = $usuarioActual
                                                    ? trim(($usuarioActual->usu_codigo ?? '') . ' ' . ($usuarioActual->usu_descripcion ?? ''))
                                                    : 'Usuario no identificado';
                                                $usuarioTexto = trim($usuarioTexto) ?: ($usuarioActual->name ?? 'Usuario no identificado');
                                            @endphp
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="usuario" value="{{ $usuarioTexto }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" disabled>
                                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Segunda fila -->
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <label for="matriz" class="form-label">Matriz:</label>
                                            <select class="form-select" id="matriz" name="coti_codigomatriz">
                                                <option value="">Seleccionar matriz...</option>
                                                @foreach($matrices as $matriz)
                                                    <option value="{{ $matriz->matriz_codigo }}" 
                                                            {{ $cotizacion->coti_codigomatriz == $matriz->matriz_codigo ? 'selected' : '' }}>
                                                        {{ trim($matriz->matriz_codigo) }} - {{ trim($matriz->matriz_descripcion) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="estado" class="form-label">Estado:</label>
                                            <select class="form-select" id="estado" name="coti_estado">
                                                @php
                                                    $estado = trim($cotizacion->coti_estado);
                                                    $estadoActual = 'E';
                                                    if(str_starts_with($estado, 'A')) {
                                                        $estadoActual = 'A';
                                                    } elseif(str_starts_with($estado, 'R')) {
                                                        $estadoActual = 'R';
                                                    } elseif(str_starts_with($estado, 'P')) {
                                                        $estadoActual = 'P';
                                                    }
                                                @endphp
                                                <option value="E" {{ $estadoActual == 'E' ? 'selected' : '' }}>En Espera</option>
                                                <option value="A" {{ $estadoActual == 'A' ? 'selected' : '' }}>Aprobado</option>
                                                <option value="R" {{ $estadoActual == 'R' ? 'selected' : '' }}>Rechazado</option>
                                                <option value="P" {{ $estadoActual == 'P' ? 'selected' : '' }}>En Proceso</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Comentarios -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label for="contacto" class="form-label">Contacto:</label>
                                            <input type="text" class="form-control" id="contacto" name="coti_contacto"
                                                   value="{{ old('coti_contacto', $cotizacion->coti_contacto) }}" placeholder="Nombre del contacto principal">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="correo" class="form-label">Correo:</label>
                                            <input type="email" class="form-control" id="correo" name="coti_mail1"
                                                   value="{{ old('coti_mail1', $cotizacion->coti_mail1) }}" placeholder="correo@cliente.com">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="telefono" class="form-label">Teléfono:</label>
                                            <input type="text" class="form-control" id="telefono" name="coti_telefono" 
                                                   value="{{ old('coti_telefono', $cotizacion->coti_telefono) }}" placeholder="+54 9 11 1234-5678">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sector" class="form-label">Sector:</label>
                                            <input type="text" class="form-control" id="sector" name="coti_sector"
                                                   value="{{ old('coti_sector', trim($cotizacion->coti_sector ?? '')) }}" placeholder="Sector del cliente">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="comentario" class="form-label">Comentario:</label>
                                            <textarea class="form-control" id="comentario" name="coti_notas" rows="3">{{ $cotizacion->coti_notas }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Sección de Descuentos -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <h5 class="mb-3">Descuentos</h5>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label for="descuento" class="form-label">Descuento Global %</label>
                                                    <input type="number" step="0.01" class="form-control" id="descuento" name="descuento" 
                                                           value="{{ old('descuento', $cotizacion->coti_descuentoglobal ?? '0.00') }}" placeholder="0.00">
                                                </div>
                                            </div>

                                            <!-- Tabla de sectores -->
                                            <div class="mb-3">
                                                <label class="form-label">Descuentos por Sector</label>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Sector</th>
                                                                <th>Porcentaje</th>
                                                                <th>Contacto</th>
                                                                <th>Observaciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>LABORATORIO</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_porcentaje" value="{{ old('sector_laboratorio_porcentaje', number_format((float)($cotizacion->coti_sector_laboratorio_pct ?? 0), 2, '.', '')) }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_contacto" value="{{ old('sector_laboratorio_contacto', $cotizacion->coti_sector_laboratorio_contacto ?? '') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_observaciones" value="{{ old('sector_laboratorio_observaciones', $cotizacion->coti_sector_laboratorio_observaciones ?? '') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>HIGIENE Y SEGURIDAD</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_higiene_porcentaje" value="{{ old('sector_higiene_porcentaje', number_format((float)($cotizacion->coti_sector_higiene_pct ?? 0), 2, '.', '')) }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_contacto" value="{{ old('sector_higiene_contacto', $cotizacion->coti_sector_higiene_contacto ?? '') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_observaciones" value="{{ old('sector_higiene_observaciones', $cotizacion->coti_sector_higiene_observaciones ?? '') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>MICROBIOLOGÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_porcentaje" value="{{ old('sector_microbiologia_porcentaje', number_format((float)($cotizacion->coti_sector_microbiologia_pct ?? 0), 2, '.', '')) }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_contacto" value="{{ old('sector_microbiologia_contacto', $cotizacion->coti_sector_microbiologia_contacto ?? '') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_observaciones" value="{{ old('sector_microbiologia_observaciones', $cotizacion->coti_sector_microbiologia_observaciones ?? '') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>CROMATOGRAFÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_porcentaje" value="{{ old('sector_cromatografia_porcentaje', number_format((float)($cotizacion->coti_sector_cromatografia_pct ?? 0), 2, '.', '')) }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_contacto" value="{{ old('sector_cromatografia_contacto', $cotizacion->coti_sector_cromatografia_contacto ?? '') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_observaciones" value="{{ old('sector_cromatografia_observaciones', $cotizacion->coti_sector_cromatografia_observaciones ?? '') }}">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla de Items/Ensayos -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>Items de la Cotización</h5>
                                                <div>
                                                    <button type="button" id="btnAbrirModalEnsayo" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAgregarEnsayo">
                                                        <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                                        Agregar Ensayo
                                                    </button>
                                                    <button type="button" id="btnAbrirModalComponente" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarComponente">
                                                        <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                                        Agregar Componente
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 80px;">Item</th>
                                                            <th style="width: 120px;">Ensayo</th>
                                                            <th>Título</th>
                                                            <th style="width: 150px;">Método</th>
                                                            <th style="width: 120px;">Detalle</th>
                                                            <th style="width: 80px;">Cantidad</th>
                                                            <th style="width: 100px;">Prec. Unit</th>
                                                            <th style="width: 100px;">Total</th>
                                                            <th style="width: 60px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaItems"></tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <td colspan="7" class="text-end fw-bold">Total:</td>
                                                            <td class="fw-bold">
                                                                <span id="totalGeneral">0.00</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="7" class="text-end text-muted">Descuento global cliente (<span id="descuentoGlobalPorcentaje">0.00%</span>):</td>
                                                            <td class="text-danger fw-semibold">
                                                                -<span id="descuentoGlobalMonto">0.00</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="7" class="text-end text-muted">Descuento sector (<span id="descuentoSectorEtiqueta">-</span>, <span id="descuentoSectorPorcentaje">0.00%</span>):</td>
                                                            <td class="text-danger fw-semibold">
                                                                -<span id="descuentoSectorMonto">0.00</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="7" class="text-end fw-bold">Total con descuento:</td>
                                                            <td class="fw-bold">
                                                                <span id="totalConDescuento">0.00</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @include('ventas.partials.cotizacion-approval-fields', ['cotizacion' => $cotizacion])
                            </div>

                            <!-- Solapa Gestión -->
                            <div class="tab-pane fade" id="gestion" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="responsable" class="form-label">Responsable:</label>
                                                <input type="text" class="form-control" id="responsable" name="coti_responsable" 
                                                       value="{{ $cotizacion->coti_responsable }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_aprobado" class="form-label">Fecha Aprobado:</label>
                                                <input type="date" class="form-control" id="fecha_aprobado" name="coti_fechaaprobado" 
                                                       value="{{ $cotizacion->coti_fechaaprobado ? $cotizacion->coti_fechaaprobado->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="aprobo" class="form-label">Aprobó:</label>
                                                <input type="text" class="form-control" id="aprobo" name="coti_aprobo" 
                                                       value="{{ $cotizacion->coti_aprobo }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_en_curso" class="form-label">Fecha En Curso:</label>
                                                <input type="date" class="form-control" id="fecha_en_curso" name="coti_fechaencurso" 
                                                       value="{{ $cotizacion->coti_fechaencurso ? $cotizacion->coti_fechaencurso->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_alta_tecnica" class="form-label">Fecha Alta Técnica:</label>
                                                <input type="date" class="form-control" id="fecha_alta_tecnica" name="coti_fechaaltatecnica" 
                                                       value="{{ $cotizacion->coti_fechaaltatecnica ? $cotizacion->coti_fechaaltatecnica->format('Y-m-d') : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Solapa Empresa -->
                            <div class="tab-pane fade" id="empresa" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                            <label for="empresa_nombre" class="form-label">Empresa:</label>
                                            <input type="text" class="form-control" id="empresa_nombre" name="coti_empresa" 
                                                       value="{{ $cotizacion->coti_empresa }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="establecimiento" class="form-label">Establecimiento:</label>
                                                <input type="text" class="form-control" id="establecimiento" name="coti_establecimiento"
                                                       value="{{ $cotizacion->coti_establecimiento }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion_cliente" class="form-label">Dirección Cliente:</label>
                                                <input type="text" class="form-control" id="direccion_cliente" name="coti_direccioncli"
                                                       value="{{ $cotizacion->coti_direccioncli }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="localidad_cliente" class="form-label">Localidad:</label>
                                                <input type="text" class="form-control" id="localidad_cliente" name="coti_localidad"
                                                       value="{{ $cotizacion->coti_localidad }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="partido" class="form-label">Partido:</label>
                                                <input type="text" class="form-control" id="partido" name="coti_partido"
                                                       value="{{ $cotizacion->coti_partido }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="cuit_cliente" class="form-label">CUIT:</label>
                                                <input type="text" class="form-control" id="cuit_cliente" name="coti_cuit"
                                                       value="{{ $cotizacion->coti_cuit }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="codigo_postal_cliente" class="form-label">Código Postal:</label>
                                                <input type="text" class="form-control" id="codigo_postal_cliente" name="coti_codigopostal"
                                                       value="{{ $cotizacion->coti_codigopostal }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                                        <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" />
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <x-heroicon-o-check style="width: 16px; height: 16px;" class="me-1" />
                                        Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{--
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda de clientes
    const clienteInput = document.getElementById('cliente_codigo');
    const clienteNombre = document.getElementById('cliente_nombre');
    const btnBuscarCliente = document.getElementById('btnBuscarCliente');
    
    let searchTimeout;
    
    // Búsqueda automática mientras se escribe
    clienteInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const termino = this.value.trim();
        
        if (termino.length < 2) {
            return;
        }
        
        searchTimeout = setTimeout(() => {
            buscarClientes(termino);
        }, 300);
    });

    // Búsqueda al hacer clic en el botón
    btnBuscarCliente.addEventListener('click', function() {
        const termino = clienteInput.value.trim();
        if (termino.length >= 2) {
            buscarClientes(termino);
        }
    });

    // Función para buscar clientes
    function buscarClientes(termino) {
        fetch(`/api/clientes/buscar?q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const coincidenciaExacta = data.find(cliente => 
                        cliente.codigo.trim().toLowerCase() === termino.toLowerCase()
                    );
                    
                    if (coincidenciaExacta) {
                        seleccionarCliente(coincidenciaExacta.codigo);
                    } else if (data.length === 1) {
                        seleccionarCliente(data[0].codigo);
                    } else {
                        mostrarOpcionesClientes(data);
                    }
                }
            })
            .catch(error => {
                console.error('Error buscando clientes:', error);
            });
    }

    // Función para seleccionar un cliente
    function seleccionarCliente(codigoCliente) {
        fetch(`/api/clientes/${encodeURIComponent(codigoCliente)}`)
            .then(response => response.json())
            .then(cliente => {
                if (cliente.error) {
                    return;
                }
                
                clienteInput.value = cliente.codigo;
                clienteNombre.value = cliente.razon_social;
                
                // Actualizar campos
                const empresaField = document.getElementById('empresa');
                const direccionField = document.getElementById('direccion_cliente');
                const localidadField = document.getElementById('localidad_cliente');
                const cuitField = document.getElementById('cuit_cliente');
                const codigoPostalField = document.getElementById('codigo_postal_cliente');
                const telefonoField = document.getElementById('telefono');
                
                if (empresaField) empresaField.value = cliente.razon_social || '';
                if (direccionField) direccionField.value = cliente.direccion || '';
                if (localidadField) localidadField.value = cliente.localidad || '';
                if (cuitField) cuitField.value = cliente.cuit || '';
                if (codigoPostalField) codigoPostalField.value = cliente.codigo_postal || '';
                if (telefonoField) telefonoField.value = cliente.telefono || '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Cliente Seleccionado',
                    text: `Se han actualizado los datos de ${cliente.razon_social}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            })
            .catch(error => {
                console.error('Error obteniendo datos del cliente:', error);
            });
    }

    // Función para mostrar opciones de clientes
    function mostrarOpcionesClientes(clientes) {
        const opciones = {};
        clientes.forEach((cliente) => {
            opciones[cliente.codigo] = cliente.text;
        });

        Swal.fire({
            title: 'Seleccionar Cliente',
            text: `Se encontraron ${clientes.length} clientes. Seleccione uno:`,
            input: 'select',
            inputOptions: opciones,
            inputPlaceholder: 'Seleccione un cliente...',
            showCancelButton: true,
            confirmButtonText: 'Seleccionar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debe seleccionar un cliente';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                seleccionarCliente(result.value);
            }
        });
    }

    // Validación del formulario
    const form = document.getElementById('cotizacionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const clienteCodigo = document.getElementById('cliente_codigo');
            if (!clienteCodigo.value.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    text: 'El código de cliente es obligatorio'
                });
                clienteCodigo.focus();
                return;
            }
            
            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Se actualizarán los datos de la cotización',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    e.preventDefault();
                }
            });
        });
    }

    // Notificaciones de sesión
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session("error") }}',
            confirmButtonColor: '#dc3545'
        });
    @endif
});
</script>
--}}

@include('ventas.partials.cotizacion-modals')
@include('ventas.partials.cotizacion-styles')

<script>
    @php
        $configuracionCotizacion = $cotizacionConfig ?? [
            'modo' => 'edit',
            'puedeEditar' => true,
            'ensayosIniciales' => [],
            'componentesIniciales' => [],
        ];
    @endphp
    window.cotizacionConfig = @json($configuracionCotizacion);
</script>

@include('ventas.partials.cotizacion-scripts')
@endsection

