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
                            <input type="number" class="form-control" id="cantidad_ensayo" name="cantidad" value="1" min="1" step="1">
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
                                   name="comp_precio_final" value="0.00">
                        </div>
                    </div>

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

<!-- Modal Editar Componente -->
<div class="modal fade" id="modalEditarComponente" tabindex="-1" aria-labelledby="modalEditarComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarComponenteLabel">Editar Componente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarComponente">
                    <input type="hidden" id="edit_componente_item_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="edit_componente_analisis" class="form-label">Análisis <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_componente_analisis" name="edit_componente_analisis" required>
                                <option value="">Seleccionar análisis...</option>
                            </select>
                            <small class="text-muted">Seleccione el análisis que desea asignar a este componente</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_componente_codigo" class="form-label">Código:</label>
                            <input type="text" class="form-control" id="edit_componente_codigo" name="edit_componente_codigo" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_componente_matriz" class="form-label">Matriz:</label>
                            <select class="form-select" id="edit_componente_matriz" name="edit_componente_matriz">
                                <option value="">Seleccionar matriz...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_componente_precio" class="form-label">Precio:</label>
                            <input type="number" step="0.01" class="form-control" id="edit_componente_precio" name="edit_componente_precio" value="0.00" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_componente_unidad" class="form-label">Unidad de Medida:</label>
                            <input type="text" class="form-control" id="edit_componente_unidad" name="edit_componente_unidad" placeholder="U.M.">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_componente_metodo" class="form-label">Método de Análisis:</label>
                            <select class="form-select" id="edit_componente_metodo" name="edit_componente_metodo">
                                <option value="">Seleccionar método...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_componente_ley" class="form-label">Ley/Normativa:</label>
                            <select class="form-select" id="edit_componente_ley" name="edit_componente_ley">
                                <option value="">Seleccionar normativa...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_componente_cantidad" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="edit_componente_cantidad" name="edit_componente_cantidad" value="1" min="1" step="1">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarComponenteEditado">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>