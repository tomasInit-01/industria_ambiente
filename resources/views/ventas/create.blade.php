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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Crear Nueva Cotización</h2>
                <div>
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary me-2">
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
                    <form method="POST" action="{{ route('ventas.store') }}" id="cotizacionForm">
                        @csrf
                        
                        <!-- Header con información básica -->
                        <div class="border-bottom px-4 py-3 bg-info">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label for="cliente_codigo" class="form-label fw-semibold mb-1 text-dark">Cliente:</label>
                                    <div class="position-relative" id="clienteBuscadorWrapper">
                                        <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" id="cliente_codigo" name="coti_codigocli" 
                                                   value="{{ old('coti_codigocli') }}" placeholder="Escribe nombre o código..." autocomplete="off" required>
                                            <button class="btn btn-outline-secondary btn-sm" style="border-color: #fff;" type="button" id="btnBuscarCliente">
                                                <x-heroicon-o-magnifying-glass style="width: 14px; height: 14px; color: #fff;" />
                                            </button>
                                        </div>
                                        <div class="dropdown-menu w-100 shadow-sm p-0" id="clienteResultados"></div>
                                    </div>
                                    <!-- Campos hidden para datos del cliente -->
                                    <input type="hidden" id="cliente_razon_social_hidden" name="cliente_razon_social">
                                    <input type="hidden" id="cliente_direccion_hidden" name="cliente_direccion">
                                    <input type="hidden" id="cliente_localidad_hidden" name="cliente_localidad">
                                    <input type="hidden" id="cliente_cuit_hidden" name="cliente_cuit">
                                    <input type="hidden" id="cliente_codigo_postal_hidden" name="cliente_codigo_postal">
                                    <input type="hidden" id="cliente_telefono_hidden" name="cliente_telefono">
                                    <input type="hidden" id="cliente_correo_hidden">
                                    <input type="hidden" id="cliente_sector_hidden">
                                    <input type="hidden" id="cliente_descuento_hidden" value="{{ old('cliente_descuento_hidden', '0.00') }}" data-descuento-global="{{ old('cliente_descuento_global', '0.00') }}" data-descuento-sector="{{ old('cliente_descuento_sector', '0.00') }}" data-sector-etiqueta="{{ trim((string) old('coti_sector', '')) }}">
                                    
                                    <!-- Campos hidden para ensayos y componentes -->
                                    <input type="hidden" id="ensayos_data" name="ensayos_data">
                                    <input type="hidden" id="componentes_data" name="componentes_data">
                                </div>
                                <div class="col-md-4">
                                    <label for="cliente_nombre" class="form-label fw-semibold mb-1">&nbsp;</label>
                                    <input type="text" class="form-control form-control-sm" id="cliente_nombre" 
                                           placeholder="Seleccione un cliente" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="sucursal" class="form-label fw-semibold mb-1 text-dark">Sucursal:</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal" name="coti_codigosuc"
                                           value="{{ old('coti_codigosuc') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="numero" class="form-label fw-semibold mb-1 text-dark">Nro:</label>
                                    <input type="text" class="form-control form-control-sm" id="numero" name="coti_num" 
                                           value="NUEVO" readonly>
                                </div>

                                <div class="col-md-2">
                                    <label for="Para" class="form-label fw-semibold mb-1 text-dark">Para:</label>
                                    <input type="text" class="form-control form-control-sm" id="coti_para" name="coti_para" 
                                           value="{{ old('coti_para') }}">
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Documentos</button>
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
                                                   value="{{ old('coti_descripcion') }}" placeholder="Descripción de la cotización...">
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
                                                   value="{{ old('coti_fechaalta', date('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_venc" class="form-label">Venc:</label>
                                            <input type="date" class="form-control" id="fecha_venc" name="coti_fechafin"
                                                   value="{{ old('coti_fechafin') }}">
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
                                                            {{ old('coti_codigomatriz') == $matriz->matriz_codigo ? 'selected' : '' }}>
                                                        {{ trim($matriz->matriz_codigo) }} - {{ trim($matriz->matriz_descripcion) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="estado" class="form-label">Estado:</label>
                                            <select class="form-select" id="estado" name="coti_estado">
                                                <option value="En Espera" selected>En Espera</option>
                                                <option value="Aprobado">Aprobado</option>
                                                <option value="Rechazado">Rechazado</option>
                                                <option value="En Proceso">En Proceso</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Comentarios -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label for="contacto" class="form-label">Contacto:</label>
                                            <input type="text" class="form-control" id="contacto" name="coti_contacto"
                                                   value="{{ old('coti_contacto') }}" placeholder="Nombre del contacto principal">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="correo" class="form-label">Correo:</label>
                                            <input type="email" class="form-control" id="correo" name="coti_mail1"
                                                   value="{{ old('coti_mail1') }}" placeholder="correo@cliente.com">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="telefono" class="form-label">Teléfono:</label>
                                            <input type="text" class="form-control" id="telefono" name="coti_telefono"
                                                   value="{{ old('coti_telefono') }}" placeholder="+54 9 11 1234-5678">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sector" class="form-label">Sector:</label>
                                            <select class="form-select" id="sector" name="coti_sector">
                                                <option value="">Seleccionar sector...</option>
                                                    @foreach($sectoresCliente as $sector)
                                                        @php
                                                            $codigoSector = trim($sector->divis_codigo);
                                                        @endphp
                                                        <option value="{{ $codigoSector }}" 
                                                                {{ trim((string) old('coti_sector')) === $codigoSector ? 'selected' : '' }}>
                                                            {{ trim($sector->divis_descripcion) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="comentario" class="form-label">Comentario:</label>
                                            <textarea class="form-control" id="comentario" name="coti_notas" rows="3"></textarea>
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
                                                           value="{{ old('descuento', '0.00') }}" placeholder="0.00">
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
                                                                           name="sector_laboratorio_porcentaje" value="{{ old('sector_laboratorio_porcentaje', '0.00') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_contacto" value="{{ old('sector_laboratorio_contacto') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_observaciones" value="{{ old('sector_laboratorio_observaciones') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>HIGIENE Y SEGURIDAD</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_higiene_porcentaje" value="{{ old('sector_higiene_porcentaje', '0.00') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_contacto" value="{{ old('sector_higiene_contacto') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_observaciones" value="{{ old('sector_higiene_observaciones') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>MICROBIOLOGÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_porcentaje" value="{{ old('sector_microbiologia_porcentaje', '0.00') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_contacto" value="{{ old('sector_microbiologia_contacto') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_observaciones" value="{{ old('sector_microbiologia_observaciones') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>CROMATOGRAFÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_porcentaje" value="{{ old('sector_cromatografia_porcentaje', '0.00') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_contacto" value="{{ old('sector_cromatografia_contacto') }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_observaciones" value="{{ old('sector_cromatografia_observaciones') }}">
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
                                
                                @include('ventas.partials.cotizacion-approval-fields')
                                </div>
                            </div>

                            <!-- Solapa Gestión -->
                            <div class="tab-pane fade" id="gestion" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="responsable" class="form-label">Responsable:</label>
                                                <input type="text" class="form-control" id="responsable" name="coti_responsable">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_aprobado" class="form-label">Fecha Aprobado:</label>
                                                <input type="date" class="form-control" id="fecha_aprobado" name="coti_fechaaprobado">
                                            </div>
                                            <div class="mb-3">
                                                <label for="aprobo" class="form-label">Aprobó:</label>
                                                <input type="text" class="form-control" id="aprobo" name="coti_aprobo">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_en_curso" class="form-label">Fecha En Curso:</label>
                                                <input type="date" class="form-control" id="fecha_en_curso" name="coti_fechaencurso">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_alta_tecnica" class="form-label">Fecha Alta Técnica:</label>
                                                <input type="date" class="form-control" id="fecha_alta_tecnica" name="coti_fechaaltatecnica">
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
                                                       value="{{ old('coti_empresa') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="establecimiento" class="form-label">Establecimiento:</label>
                                                <input type="text" class="form-control" id="establecimiento" name="coti_establecimiento"
                                                       value="{{ old('coti_establecimiento') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion_cliente" class="form-label">Dirección Cliente:</label>
                                                <input type="text" class="form-control" id="direccion_cliente" name="coti_direccioncli"
                                                       value="{{ old('coti_direccioncli') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="localidad_cliente" class="form-label">Localidad:</label>
                                                <input type="text" class="form-control" id="localidad_cliente" name="coti_localidad"
                                                       value="{{ old('coti_localidad') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="partido" class="form-label">Partido:</label>
                                                <input type="text" class="form-control" id="partido" name="coti_partido"
                                                       value="{{ old('coti_partido') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="cuit_cliente" class="form-label">CUIT:</label>
                                                <input type="text" class="form-control" id="cuit_cliente" name="coti_cuit"
                                                       value="{{ old('coti_cuit') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="codigo_postal_cliente" class="form-label">Código Postal:</label>
                                                <input type="text" class="form-control" id="codigo_postal_cliente" name="coti_codigopostal"
                                                       value="{{ old('coti_codigopostal') }}">
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
                                    <button type="button" class="btn btn-info">
                                        <x-heroicon-o-arrow-path style="width: 16px; height: 16px;" class="me-1" />
                                        Salir
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <x-heroicon-o-check style="width: 16px; height: 16px;" class="me-1" />
                                        Guardar
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

<!-- Modal Agregar Ensayo -->
<div class="modal fade" id="modalAgregarEnsayo" tabindex="-1" aria-labelledby="modalAgregarEnsayoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarEnsayoLabel">Ensayo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEnsayo">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="ensayo_muestra" class="form-label">Seleccionar Muestra/Ensayo <span class="text-danger">*</span></label>
                            <select class="form-select" id="ensayo_muestra" name="ensayo_muestra" required>
                                <option value="">Seleccionar muestra...</option>
                            </select>
                            <small class="text-muted">Seleccione el tipo de muestra que desea analizar</small>
                            <div id="ensayo_metodo_info" class="form-text mt-1"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ensayo_codigo" class="form-label">Código:</label>
                            <input type="text" class="form-control" id="ensayo_codigo" name="ensayo_codigo" 
                                   placeholder="Se generará automáticamente" readonly>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="no_requiere_custodia">
                                <label class="form-check-label" for="no_requiere_custodia">
                                    No Requiere Cadena de Custodia
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="cantidad_ensayo" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="cantidad_ensayo" name="cantidad" value="3" min="1">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="flexible">
                                <label class="form-check-label" for="flexible">Flexible</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="bonificado">
                                <label class="form-check-label" for="bonificado">Bonificado</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ensayo_ley_normativa" class="form-label">Ley/Normativa:</label>
                            <select class="form-select" id="ensayo_ley_normativa" name="ensayo_ley_normativa">
                                <option value="">Seleccionar normativa...</option>
                            </select>
                        </div>
                    </div>


                    <!-- Notas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_imprimible" value="imprimible" checked>
                                <label class="form-check-label" for="nota_imprimible">Nota Imprimible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_interna" value="interna">
                                <label class="form-check-label" for="nota_interna">Nota Interna</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_fact" value="fact">
                                <label class="form-check-label" for="nota_fact">Nota Fact.</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2">Insertar Nota Predefinida</button>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="predeterminar">
                                <label class="form-check-label" for="predeterminar">Predeterminar</label>
                            </div>
                            <textarea class="form-control" rows="4" placeholder="Comprende el análisis puntual de calidad de aire exterior en sitios sobre a definir en función de los vientos predominantes para la determinación de MP TOTAL (EPA O 2 1) o MP10 (EPA O 2 3)."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEnsayo">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Componente -->
<div class="modal fade" id="modalAgregarComponente" tabindex="-1" aria-labelledby="modalAgregarComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalAgregarComponenteLabel">Componente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formComponente">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="componente_ensayo_asociado" class="form-label">Ensayo Asociado <span class="text-danger">*</span></label>
                            <select class="form-select" id="componente_ensayo_asociado" name="componente_ensayo_asociado" required>
                                <option value="">Seleccionar ensayo...</option>
                            </select>
                            <small class="text-muted">
                                <x-heroicon-o-information-circle style="width: 14px; height: 14px;" class="me-1" />
                                Seleccione el ensayo al que pertenece este análisis. Debe agregar al menos un ensayo primero.
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label for="componente_analisis" class="form-label">Seleccionar Análisis <span class="text-danger">*</span></label>
                            <select class="form-select" id="componente_analisis" name="componente_analisis[]" multiple required>
                                <option disabled value="">Seleccionar análisis...</option>
                            </select>
                            <small class="text-muted">
                                <x-heroicon-o-beaker style="width: 14px; height: 14px;" class="me-1" />
                                Seleccione uno o varios análisis a realizar en la muestra
                            </small>
                            <div id="componente_metodo_info" class="form-text mt-1"></div>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="componente_codigo" class="form-label">Código:</label>
                            <input type="text" class="form-control" id="componente_codigo" name="componente_codigo" 
                                   placeholder="Se generará automáticamente" readonly>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_no_requiere_custodia">
                                <label class="form-check-label" for="comp_no_requiere_custodia">
                                    No Requiere Cadena de Custodia
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_flexible">
                                <label class="form-check-label" for="comp_flexible">Flexible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_bonificado">
                                <label class="form-check-label" for="comp_bonificado">Bonificado</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Cotización</small>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Factura</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="comp_precio_final" class="form-label">Precio:</label>
                            <input type="number" step="0.01" class="form-control" id="comp_precio_final" 
                                   name="comp_precio_final" value="237055.00">
                        </div>
                        {{-- <div class="col-md-8">
                            <label class="form-label">Precio de lista</label>
                        </div> --}}
                    </div>

                    <!-- Notas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_imprimible" value="imprimible" checked>
                                <label class="form-check-label" for="comp_nota_imprimible">Nota Imprimible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_interna" value="interna">
                                <label class="form-check-label" for="comp_nota_interna">Nota Interna</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_fact" value="fact">
                                <label class="form-check-label" for="comp_nota_fact">Nota Fact.</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2">Insertar Nota Predefinida</button>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="comp_predeterminar">
                                <label class="form-check-label" for="comp_predeterminar">Predeterminar</label>
                            </div>
                            <textarea class="form-control" rows="4" placeholder="Descripción del componente..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarComponente">Aceptar</button>
            </div>
        </div>
    </div>
</div>

@include('ventas.partials.cotizacion-styles')

<script>
    @php
        $configuracionCotizacion = $cotizacionConfig ?? [
            'modo' => 'create',
            'puedeEditar' => true,
            'ensayosIniciales' => [],
            'componentesIniciales' => [],
        ];
    @endphp
    window.cotizacionConfig = @json($configuracionCotizacion);
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('ventas.partials.cotizacion-scripts')
@endsection

