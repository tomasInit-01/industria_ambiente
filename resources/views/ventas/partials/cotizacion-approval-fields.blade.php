@php
    $cotizacionActual = $cotizacion ?? null;

    $estadoFuente = old('coti_estado');
    if (! $estadoFuente && $cotizacionActual) {
        $estadoFuente = $cotizacionActual->coti_estado;
    }

    $estadoNormalizado = strtoupper(substr(trim((string) $estadoFuente), 0, 1));
    $mostrarDatosAprobacion = $estadoNormalizado === 'A';

    $remitoTipoRaw = old('coti_referencia_tipo', optional($cotizacionActual)->coti_referencia_tipo);
    $remitoTipo = strtolower(trim((string) $remitoTipoRaw));
    $remitoValor = old('coti_referencia_valor', optional($cotizacionActual)->coti_referencia_valor);

    $ocValor = old('coti_oc_referencia', optional($cotizacionActual)->coti_oc_referencia);

    $hesHasTipoRaw = old('coti_hes_has_tipo', optional($cotizacionActual)->coti_hes_has_tipo);
    $hesHasTipo = strtoupper(trim((string) $hesHasTipoRaw));
    $hesHasValor = old('coti_hes_has_valor', optional($cotizacionActual)->coti_hes_has_valor);

    $grContratoTipoRaw = old('coti_gr_contrato_tipo', optional($cotizacionActual)->coti_gr_contrato_tipo);
    $grContratoTipo = strtoupper(trim((string) $grContratoTipoRaw));
    $grContratoValor = old('coti_gr_contrato', optional($cotizacionActual)->coti_gr_contrato);

    $otroValor = old('coti_otro_referencia', optional($cotizacionActual)->coti_otro_referencia);
@endphp

@php
    $wrapperVisible = $mostrarDatosAprobacion ? '1' : '0';
@endphp

<div id="datosAprobacionWrapper" data-visible="{{ $wrapperVisible }}">
<div id="datosAprobacionCard" class="card mt-4" style="{{ $mostrarDatosAprobacion ? '' : 'display:none;' }}">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0">Datos de la cotización</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="coti_referencia_tipo" class="form-label">Remito / N° Pedido</label>
                <div class="input-group">
                    <select class="form-select" id="coti_referencia_tipo" name="coti_referencia_tipo">
                        <option value="">Seleccionar...</option>
                        <option value="remito" {{ $remitoTipo === 'remito' ? 'selected' : '' }}>Remito</option>
                        <option value="pedido" {{ $remitoTipo === 'pedido' ? 'selected' : '' }}>N° Pedido</option>
                    </select>
                    <input type="text"
                           class="form-control"
                           name="coti_referencia_valor"
                           value="{{ $remitoValor }}"
                           placeholder="Ingrese el número correspondiente">
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="coti_oc_referencia" class="form-label">O.C.</label>
                <input type="text"
                       class="form-control"
                       id="coti_oc_referencia"
                       name="coti_oc_referencia"
                       value="{{ $ocValor }}"
                       placeholder="Ingrese la orden de compra">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="coti_hes_has_tipo" class="form-label">HES / HAS</label>
                <div class="input-group">
                    <select class="form-select" id="coti_hes_has_tipo" name="coti_hes_has_tipo">
                        <option value="">Seleccionar...</option>
                        <option value="HES" {{ $hesHasTipo === 'HES' ? 'selected' : '' }}>HES</option>
                        <option value="HAS" {{ $hesHasTipo === 'HAS' ? 'selected' : '' }}>HAS</option>
                    </select>
                    <input type="text"
                           class="form-control"
                           name="coti_hes_has_valor"
                           value="{{ $hesHasValor }}"
                           placeholder="Ingrese la referencia">
                </div>
            </div>
        </div>

        <div class="row g-3 align-items-end mt-1">
            <div class="col-md-6 col-lg-4">
                <label for="coti_gr_contrato_tipo" class="form-label">GR / N° Contrato</label>
                <div class="input-group">
                    <select class="form-select" id="coti_gr_contrato_tipo" name="coti_gr_contrato_tipo">
                        <option value="">Seleccionar...</option>
                        <option value="GR" {{ $grContratoTipo === 'GR' ? 'selected' : '' }}>GR</option>
                        <option value="CONTRATO" {{ $grContratoTipo === 'CONTRATO' ? 'selected' : '' }}>N° Contrato</option>
                        <option value="OTRO" {{ $grContratoTipo === 'OTRO' ? 'selected' : '' }}>Otro</option>
                    </select>
                    <input type="text"
                           class="form-control"
                           id="coti_gr_contrato"
                           name="coti_gr_contrato"
                           value="{{ $grContratoValor }}"
                           placeholder="Ingrese el número seleccionado">
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="coti_otro_referencia" class="form-label">Otro</label>
                <input type="text"
                       class="form-control"
                       id="coti_otro_referencia"
                       name="coti_otro_referencia"
                       value="{{ $otroValor }}"
                       placeholder="Referencia adicional (opcional)">
            </div>
        </div>
    </div>
</div>
</div>

