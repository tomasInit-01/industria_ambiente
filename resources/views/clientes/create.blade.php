@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Crear Nuevo Cliente</h2>
                <div>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary me-2">
                        <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" />
                        Volver
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('clientes.store') }}" id="clienteForm">
                        @csrf
                        
                        <!-- Header con código y estado -->
                        <div class="border-bottom px-4 py-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label for="codigo" class="form-label fw-semibold mb-1">Código:</label>
                                    <input type="text" class="form-control form-control-sm" id="codigo" name="codigo" 
                                           value="{{ old('codigo') }}" placeholder="Autogenerado">
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <label class="form-label fw-semibold mb-0 me-3">Estado:</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="activo" id="activo_si" value="1" checked>
                                            <label class="form-check-label" for="activo_si">Sí</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="activo" id="activo_no" value="0">
                                            <label class="form-check-label" for="activo_no">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación de solapas -->
                        <ul class="nav nav-tabs nav-tabs-custom" id="clienteTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                        data-bs-target="#general" type="button" role="tab">
                                    General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="facturacion-tab" data-bs-toggle="tab" 
                                        data-bs-target="#facturacion" type="button" role="tab">
                                    Facturación
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Contactos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Cobranza</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Observaciones</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Actividades</button>
                            </li>
                        </ul>

                        <!-- Contenido de las solapas -->
                        <div class="tab-content" id="clienteTabsContent">
                            <!-- Solapa General -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="razon_social" class="form-label">Razón Social</label>
                                                <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                                       value="{{ old('razon_social') }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="fantasia" class="form-label">Fantasía</label>
                                                <input type="text" class="form-control" id="fantasia" name="fantasia" 
                                                       value="{{ old('fantasia') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="direccion" class="form-label">Dirección</label>
                                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                                       value="{{ old('direccion') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad</label>
                                                <input type="text" class="form-control" id="localidad" name="localidad" 
                                                       value="{{ old('localidad') }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="provincia" class="form-label">Provincia</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="provincia_codigo" name="provincia_codigo" 
                                                                   value="{{ old('provincia_codigo') }}" placeholder="Código">
                                                            <input type="text" class="form-control" id="provincia_nombre" name="provincia_nombre" 
                                                                   value="{{ old('provincia_nombre') }}" placeholder="Nombre">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="zona" class="form-label">Zona:</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="zona_codigo" name="zona_codigo" 
                                                                   value="{{ old('zona_codigo') }}" placeholder="Código">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="autoriza" class="form-label">Autoriza:</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="autoriza_codigo" name="autoriza_codigo" 
                                                                   value="{{ old('autoriza_codigo') }}" placeholder="Código">
                                                            <input type="text" class="form-control" id="autoriza_nombre" name="autoriza_nombre" 
                                                                   value="{{ old('autoriza_nombre') }}" placeholder="Nombre">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="rubro" class="form-label">Rubro</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="rubro_codigo" name="rubro_codigo" 
                                                                   value="{{ old('rubro_codigo') }}" placeholder="Código">
                                                            <input type="text" class="form-control" id="rubro_nombre" name="rubro_nombre" 
                                                                   value="{{ old('rubro_nombre') }}" placeholder="Descripción">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="nro_carpeta" class="form-label">Nro.Carpeta</label>
                                                <input type="text" class="form-control" id="nro_carpeta" name="nro_carpeta" 
                                                       value="{{ old('nro_carpeta') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="documentacion" class="form-label">Documentación</label>
                                                <textarea class="form-control" id="documentacion" name="documentacion" rows="4">{{ old('documentacion') }}</textarea>
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                                       value="{{ old('codigo_postal') }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="pais_codigo" class="form-label">País</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="pais_codigo" name="pais_codigo" 
                                                                   value="{{ old('pais_codigo', 'ARG') }}" placeholder="ARG">
                                                            <input type="text" class="form-control" id="pais_nombre" name="pais_nombre" 
                                                                   value="{{ old('pais_nombre', 'ARGENTINA') }}" placeholder="ARGENTINA">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="zona_comercial" class="form-label">Zona Comercial</label>
                                                <input type="text" class="form-control" id="zona_comercial" name="zona_comercial" 
                                                       value="{{ old('zona_comercial') }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="promotor" class="form-label">Promotor</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="promotor_codigo" name="promotor_codigo" 
                                                                   value="{{ old('promotor_codigo') }}">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha_alta" class="form-label">Fecha alta</label>
                                                <input type="date" class="form-control" id="fecha_alta" name="fecha_alta" 
                                                       value="{{ old('fecha_alta', date('Y-m-d')) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha_modif" class="form-label">Fecha modif.</label>
                                                <input type="date" class="form-control" id="fecha_modif" name="fecha_modif" 
                                                       value="{{ old('fecha_modif') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Solapa Facturación -->
                            <div class="tab-pane fade" id="facturacion" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="condicion_iva" class="form-label">Condición de I.V.A.</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="condicion_iva_codigo" name="condicion_iva_codigo" 
                                                                   value="{{ old('condicion_iva_codigo') }}" placeholder="Código">
                                                            <input type="text" class="form-control" id="condicion_iva_desc" name="condicion_iva_desc" 
                                                                   value="{{ old('condicion_iva_desc') }}" placeholder="Descripción">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="condicion_pago" class="form-label">Condición de pago</label>
                                                <input type="text" class="form-control" id="condicion_pago" name="condicion_pago" 
                                                       value="{{ old('condicion_pago') }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="tipo" class="form-label">Tipo</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="tipo_codigo" name="tipo_codigo" 
                                                                   value="{{ old('tipo_codigo') }}" placeholder="Código">
                                                            <input type="text" class="form-control" id="tipo_desc" name="tipo_desc" 
                                                                   value="{{ old('tipo_desc') }}" placeholder="Descripción">
                                                            <button class="btn btn-outline-secondary" type="button">
                                                                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="lista_precios" class="form-label">Lista de Precios</label>
                                                <input type="text" class="form-control" id="lista_precios" name="lista_precios" 
                                                       value="{{ old('lista_precios') }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="nro_lp" class="form-label">Nro LP</label>
                                                        <select class="form-select" id="nro_lp" name="nro_lp">
                                                            <option value="">Seleccionar...</option>
                                                            <option value="1" {{ old('nro_lp') == '1' ? 'selected' : '' }}>1</option>
                                                            <option value="2" {{ old('nro_lp') == '2' ? 'selected' : '' }}>2</option>
                                                            <option value="3" {{ old('nro_lp') == '3' ? 'selected' : '' }}>3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="cuit_doc" class="form-label">C.U.I.T./Doc</label>
                                                        <div class="input-group">
                                                            <select class="form-select" id="cuit_tipo" name="cuit_tipo" style="max-width: 100px;">
                                                                <option value="CUIT" {{ old('cuit_tipo') == 'CUIT' ? 'selected' : '' }}>CUIT</option>
                                                                <option value="CUIL" {{ old('cuit_tipo') == 'CUIL' ? 'selected' : '' }}>CUIL</option>
                                                                <option value="DNI" {{ old('cuit_tipo') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                            </select>
                                                            <input type="text" class="form-control" id="cuit_numero" name="cuit_numero" 
                                                                   value="{{ old('cuit_numero') }}" placeholder="Número">
                                                            <button class="btn btn-outline-secondary" type="button">Padrón AFIP</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="tipo_factura" class="form-label">Tipo de Factura:</label>
                                                <select class="form-select" id="tipo_factura" name="tipo_factura">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="A" {{ old('tipo_factura') == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B" {{ old('tipo_factura') == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="C" {{ old('tipo_factura') == 'C' ? 'selected' : '' }}>C</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="descuento" class="form-label">Descuento %</label>
                                                        <input type="number" step="0.01" class="form-control" id="descuento" name="descuento" 
                                                               value="{{ old('descuento', '0.00') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tabla de sectores -->
                                            <div class="mb-3">
                                                <label class="form-label">Factor</label>
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
                                                                           name="sector_laboratorio_porcentaje" value="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_contacto">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_laboratorio_observaciones">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>HIGIENE Y SEGURIDAD</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_higiene_porcentaje" value="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_contacto">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_higiene_observaciones">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>MICROBIOLOGÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_porcentaje" value="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_contacto">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_microbiologia_observaciones">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>CROMATOGRAFÍA</td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_porcentaje" value="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_contacto">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="sector_cromatografia_observaciones">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="observaciones_facturacion" class="form-label">Observaciones</label>
                                                <textarea class="form-control" id="observaciones_facturacion" name="observaciones_facturacion" 
                                                          rows="25">{{ old('observaciones_facturacion') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" />
                                    Cancelar
                                </button>
                                {{-- <button type="button" class="btn btn-success">
                                    <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                    Agregar
                                </button>
                                <button type="button" class="btn btn-warning">
                                    <x-heroicon-o-pencil style="width: 16px; height: 16px;" class="me-1" />
                                    Modificar
                                </button> --}}
                                <button type="submit" class="btn btn-primary">
                                    <x-heroicon-o-check style="width: 16px; height: 16px;" class="me-1" />
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados para las solapas */
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
        padding: 0;
        margin: 0;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 0;
        padding: 12px 20px;
        color: #495057;
        background-color: transparent;
        font-weight: 500;
        position: relative;
    }

    .nav-tabs-custom .nav-link:hover {
        background-color: #e9ecef;
        border: none;
    }

    .nav-tabs-custom .nav-link.active {
        background-color: #fff;
        color: #0d6efd;
        border: none;
        border-bottom: 2px solid #0d6efd;
    }

    .nav-tabs-custom .nav-link.disabled {
        color: #6c757d;
        background-color: transparent;
        cursor: not-allowed;
    }

    /* Estilo para los campos de formulario */
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Tabla de sectores */
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.5rem;
    }

    .table td {
        padding: 0.25rem 0.5rem;
        vertical-align: middle;
    }

    /* Botones de búsqueda */
    .btn-outline-secondary {
        border-color: #ced4da;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    /* Header del formulario */
    .bg-light {
        background-color: #f8f9fa !important;
    }

    /* Radio buttons en línea */
    .form-check-inline .form-check-input {
        margin-right: 0.25rem;
    }

    .form-check-inline .form-check-label {
        margin-right: 1rem;
    }

    /* Espaciado de contenido */
    .tab-content {
        min-height: 500px;
    }

    /* Input groups */
    .input-group .form-control {
        border-right: 0;
    }

    .input-group .form-control:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .form-control:not(:first-child) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
    }

    .input-group .btn {
        border-left: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips si están disponibles
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        if (tooltipTriggerList.length > 0) {
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Manejar cambios en las solapas
        const tabs = document.querySelectorAll('#clienteTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                // Aquí se puede agregar lógica adicional cuando se cambie de solapa
                console.log('Solapa activa:', e.target.textContent.trim());
            });
        });

        // Validación básica del formulario
        const form = document.getElementById('clienteForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const razonSocial = document.getElementById('razon_social');
                if (!razonSocial.value.trim()) {
                    e.preventDefault();
                    alert('La Razón Social es obligatoria');
                    razonSocial.focus();
                    return;
                }
            });
        }
    });
</script>
@endsection