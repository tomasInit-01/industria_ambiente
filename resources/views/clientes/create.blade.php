@extends('layouts.app')

@section('content')

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
                                <button class="nav-link" id="contactos-tab" data-bs-toggle="tab" 
                                        data-bs-target="#contactos" type="button" role="tab">
                                    Contactos
                                </button>
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="empresas-relacionadas-tab" data-bs-toggle="tab" 
                                        data-bs-target="#empresas-relacionadas" type="button" role="tab">
                                    Empresas Relacionadas
                                </button>
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
                                                <label for="razon_social" class="form-label">Razón Social <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                                       id="razon_social" name="razon_social" 
                                                       value="{{ old('razon_social') }}" required>
                                                @error('razon_social')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label d-block">Estado</label>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="activo" id="estado_activo_si" value="1"
                                                            {{ old('activo', '1') == '1' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="estado_activo_si">Activo</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="activo" id="estado_activo_no" value="0"
                                                            {{ old('activo') == '0' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="estado_activo_no">Inactivo</label>
                                                    </div>
                                                </div>
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
                                                <label for="partido" class="form-label">Partido</label>
                                                <input type="text" class="form-control" id="partido" name="partido" 
                                                       value="{{ old('partido') }}">
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
                                                                   value="{{ old('provincia_codigo') }}" placeholder="Código (máx. 5 caracteres)" maxlength="5">
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
                                                        <select class="form-select" id="zona_codigo" name="zona_codigo">
                                                            <option value="">Seleccionar zona...</option>
                                                            @foreach($zonas as $zona)
                                                                <option value="{{ $zona->zon_codigo }}" 
                                                                        {{ old('zona_codigo') == $zona->zon_codigo ? 'selected' : '' }}>
                                                                    {{ trim($zona->zon_codigo) }} - {{ trim($zona->zon_descripcion) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="rubro" class="form-label">Rubro</label>
                                                        <select class="form-select" id="rubro_codigo" name="rubro_codigo">
                                                            <option value="">Seleccionar rubro...</option>
                                                            <option value="001" data-descripcion="INDUSTRIA ALIMENTARIA" {{ old('rubro_codigo') == '001' ? 'selected' : '' }}>001 - INDUSTRIA ALIMENTARIA</option>
                                                            <option value="002" data-descripcion="INDUSTRIA FARMACÉUTICA" {{ old('rubro_codigo') == '002' ? 'selected' : '' }}>002 - INDUSTRIA FARMACÉUTICA</option>
                                                            <option value="003" data-descripcion="INDUSTRIA QUÍMICA" {{ old('rubro_codigo') == '003' ? 'selected' : '' }}>003 - INDUSTRIA QUÍMICA</option>
                                                            <option value="004" data-descripcion="INDUSTRIA TEXTIL" {{ old('rubro_codigo') == '004' ? 'selected' : '' }}>004 - INDUSTRIA TEXTIL</option>
                                                            <option value="005" data-descripcion="HIGIENE Y SEGURIDAD" {{ old('rubro_codigo') == '005' ? 'selected' : '' }}>005 - HIGIENE Y SEGURIDAD</option>
                                                            <option value="006" data-descripcion="CONSTRUCCIÓN" {{ old('rubro_codigo') == '006' ? 'selected' : '' }}>006 - CONSTRUCCIÓN</option>
                                                            <option value="007" data-descripcion="MINERÍA" {{ old('rubro_codigo') == '007' ? 'selected' : '' }}>007 - MINERÍA</option>
                                                            <option value="008" data-descripcion="PETRÓLEO Y GAS" {{ old('rubro_codigo') == '008' ? 'selected' : '' }}>008 - PETRÓLEO Y GAS</option>
                                                            <option value="009" data-descripcion="AGRICULTURA" {{ old('rubro_codigo') == '009' ? 'selected' : '' }}>009 - AGRICULTURA</option>
                                                            <option value="010" data-descripcion="SERVICIOS" {{ old('rubro_codigo') == '010' ? 'selected' : '' }}>010 - SERVICIOS</option>
                                                            <option value="026" data-descripcion="CONSULTORÍA" {{ old('rubro_codigo') == '026' ? 'selected' : '' }}>026 - CONSULTORÍA</option>
                                                        </select>
                                                        <input type="hidden" id="rubro_nombre" name="rubro_nombre" value="{{ old('rubro_nombre') }}">
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
                                                        <label for="pais_codigo" class="form-label">País (máx. 3 caracteres)</label>
                                                            <input type="text" class="form-control" id="pais_codigo" name="pais_codigo" 
                                                                   value="{{ old('pais_codigo', 'ARG') }}" placeholder="ARG" maxlength="3">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="zona_comercial" class="form-label">Zona Comercial</label>
                                                <input type="text" class="form-control" id="zona_comercial" name="zona_comercial" 
                                                       value="{{ old('zona_comercial') }}">
                                            </div>

                                            {{-- <div class="row">
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
                                            </div> --}}

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

                            <!-- Solapa Contactos -->
                            <div class="tab-pane fade" id="contactos" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                                       value="{{ old('telefono') }}" maxlength="30">
                                            </div>

                                            <div class="mb-3">
                                                <label for="telefono1" class="form-label">Teléfono 1</label>
                                                <input type="text" class="form-control" id="telefono1" name="telefono1" 
                                                       value="{{ old('telefono1') }}" maxlength="20">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="horario1" class="form-label">Horario 1</label>
                                                        <input type="text" class="form-control" id="horario1" name="horario1" 
                                                               value="{{ old('horario1') }}" maxlength="10" placeholder="Ej: 09:00-18:00">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="horario2" class="form-label">Horario 2</label>
                                                        <input type="text" class="form-control" id="horario2" name="horario2" 
                                                               value="{{ old('horario2') }}" maxlength="10" placeholder="Ej: 09:00-18:00">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="fax" class="form-label">Fax</label>
                                                <input type="text" class="form-control" id="fax" name="fax" 
                                                       value="{{ old('fax') }}" maxlength="30">
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="{{ old('email') }}" maxlength="30">
                                            </div>

                                            <div class="mb-3">
                                                <label for="email2" class="form-label">Email 2</label>
                                                <input type="email" class="form-control" id="email2" name="email2" 
                                                       value="{{ old('email2') }}" maxlength="30">
                                            </div>

                                            <div class="mb-3">
                                                <label for="webpage" class="form-label">Página Web</label>
                                                <input type="url" class="form-control" id="webpage" name="webpage" 
                                                       value="{{ old('webpage') }}" maxlength="50" placeholder="Ej: https://www.ejemplo.com">
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
                                                        <select class="form-select" id="condicion_iva_codigo" name="condicion_iva_codigo">
                                                            <option value="">Seleccionar condición de IVA...</option>
                                                            @foreach($condicionesIva as $condicion)
                                                                <option value="{{ $condicion->civa_codigo }}" 
                                                                        data-descripcion="{{ trim($condicion->civa_descripcion) }}"
                                                                        {{ old('condicion_iva_codigo') == $condicion->civa_codigo ? 'selected' : '' }}>
                                                                    {{ trim($condicion->civa_codigo) }} - {{ trim($condicion->civa_descripcion) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" id="condicion_iva_desc" name="condicion_iva_desc" value="{{ old('condicion_iva_desc') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="condicion_pago" class="form-label">Condición de pago</label>
                                                <select class="form-select" id="condicion_pago" name="condicion_pago">
                                                    <option value="">Seleccionar condición de pago...</option>
                                                    @foreach($condicionesPago as $condicion)
                                                        <option value="{{ $condicion->pag_codigo }}" 
                                                                data-descripcion="{{ trim($condicion->pag_descripcion) }}"
                                                                {{ old('condicion_pago') == $condicion->pag_codigo ? 'selected' : '' }}>
                                                            {{ trim($condicion->pag_codigo) }} - {{ trim($condicion->pag_descripcion) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="lista_precios" class="form-label">Lista de Precios</label>
                                                <select class="form-select" id="lista_precios" name="lista_precios">
                                                    <option value="">Seleccionar lista de precios...</option>
                                                    @foreach($listasPrecios as $lista)
                                                        <option value="{{ $lista->lp_codigo }}" 
                                                                {{ old('lista_precios') == $lista->lp_codigo ? 'selected' : '' }}>
                                                            {{ trim($lista->lp_codigo) }} - {{ trim($lista->lp_descripcion) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="tipo_cliente" class="form-label">Tipo Cliente</label>
                                                        <select class="form-select" id="tipo_cliente" name="tipo_cliente">
                                                            <option value="">Seleccionar tipo de cliente...</option>
                                                            @foreach($tiposCliente as $tipo)
                                                                <option value="{{ $tipo->tcli_codigo }}" 
                                                                        {{ old('tipo_cliente') == $tipo->tcli_codigo ? 'selected' : '' }}>
                                                                    {{ trim($tipo->tcli_codigo) }} - {{ trim($tipo->tcli_descripcion) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="row">
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
                                            </div> --}}

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
                                                    <option value="Contra Informe" {{ old('tipo_factura') == 'Contra Informe' ? 'selected' : '' }}>Contra Informe</option>
                                                </select>
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

                            <!-- Solapa Empresas Relacionadas -->
                            <div class="tab-pane fade" id="empresas-relacionadas" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rel_empresa_razon_social" class="form-label">Razón Social</label>
                                                <input type="text" class="form-control" id="rel_empresa_razon_social" name="rel_empresa_razon_social" 
                                                       value="{{ old('rel_empresa_razon_social') }}" maxlength="255">
                                            </div>

                                            <div class="mb-3">
                                                <label for="rel_empresa_cuit" class="form-label">CUIT</label>
                                                <input type="text" class="form-control" id="rel_empresa_cuit" name="rel_empresa_cuit" 
                                                       value="{{ old('rel_empresa_cuit') }}" maxlength="13">
                                            </div>

                                            <div class="mb-3">
                                                <label for="rel_empresa_direcciones" class="form-label">Direcciones</label>
                                                <textarea class="form-control" id="rel_empresa_direcciones" name="rel_empresa_direcciones" 
                                                          rows="3">{{ old('rel_empresa_direcciones') }}</textarea>
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rel_empresa_localidad" class="form-label">Localidad</label>
                                                <input type="text" class="form-control" id="rel_empresa_localidad" name="rel_empresa_localidad" 
                                                       value="{{ old('rel_empresa_localidad') }}" maxlength="50">
                                            </div>

                                            <div class="mb-3">
                                                <label for="rel_empresa_partido" class="form-label">Partido</label>
                                                <input type="text" class="form-control" id="rel_empresa_partido" name="rel_empresa_partido" 
                                                       value="{{ old('rel_empresa_partido') }}" maxlength="50">
                                            </div>

                                            <div class="mb-3">
                                                <label for="rel_empresa_contacto" class="form-label">Contacto</label>
                                                <input type="text" class="form-control" id="rel_empresa_contacto" name="rel_empresa_contacto" 
                                                       value="{{ old('rel_empresa_contacto') }}" maxlength="100">
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

        // Manejar cambios en los selectores
        const condicionIvaSelect = document.getElementById('condicion_iva_codigo');
        const condicionIvaDesc = document.getElementById('condicion_iva_desc');
        
        if (condicionIvaSelect && condicionIvaDesc) {
            condicionIvaSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    condicionIvaDesc.value = selectedOption.getAttribute('data-descripcion') || '';
                } else {
                    condicionIvaDesc.value = '';
                }
            });
        }

        // Manejar cambios en el selector de rubros
        const rubroSelect = document.getElementById('rubro_codigo');
        const rubroNombre = document.getElementById('rubro_nombre');
        
        if (rubroSelect && rubroNombre) {
            rubroSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    rubroNombre.value = selectedOption.getAttribute('data-descripcion') || '';
                } else {
                    rubroNombre.value = '';
                }
            });
        }

    // Validación básica del formulario con SweetAlert
    const form = document.getElementById('clienteForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== VALIDANDO FORMULARIO DE CLIENTE ===');
            console.log('Datos del formulario:');
            
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            const razonSocial = document.getElementById('razon_social');
            if (!razonSocial.value.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    text: 'La Razón Social es obligatoria',
                    confirmButtonColor: '#dc3545'
                });
                razonSocial.focus();
                return;
            }
            
            console.log('Formulario válido, enviando...');
        });
    }

        // Mejorar la experiencia con los selectores
        const selectores = document.querySelectorAll('.form-select');
        selectores.forEach(select => {
            select.addEventListener('focus', function() {
                this.style.borderColor = '#86b7fe';
            });
            
            select.addEventListener('blur', function() {
                this.style.borderColor = '#ced4da';
            });
        });
    });
</script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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

@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Error de Validación',
        html: '<ul style="text-align: left;"><li>' + {{ Js::from($errors->all()) }}.join('</li><li>') + '</li></ul>',
        confirmButtonColor: '#dc3545'
    });
@endif

// Función de debugging (disponible en consola del navegador)
window.debugFormulario = function() {
    console.log('=== DEBUG FORMULARIO CLIENTE ===');
    const form = document.getElementById('clienteForm');
    
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    console.log('Todos los campos del formulario:');
    
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    // Validar campos críticos
    const razonSocial = document.getElementById('razon_social');
    const codigo = document.getElementById('codigo');
    const activo = document.querySelector('input[name="activo"]:checked');
    
    console.log('\nCampos críticos:');
    console.log('  - Razón Social:', razonSocial ? razonSocial.value : 'NO ENCONTRADO');
    console.log('  - Código:', codigo ? codigo.value : 'NO ENCONTRADO');
    console.log('  - Estado:', activo ? activo.value : 'NO SELECCIONADO');
    
    console.log('\nFormulario válido:', razonSocial && razonSocial.value.trim() ? 'SÍ' : 'NO');
};
</script>
@endsection