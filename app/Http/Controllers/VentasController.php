<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Ventas;
use App\Models\Coti;
use App\Models\Cotio;
use App\Models\CotioItems;
use App\Models\Clientes;
use App\Models\Matriz;
use App\Models\Divis;
use App\Models\CondicionPago;
use App\Models\ListaPrecio;
use App\Models\Metodo;
use App\Models\MetodoAnalisis;
use App\Models\MetodoMuestreo;
use App\Models\LeyNormativa;

class VentasController extends Controller {
    
    /**
     * Helper para truncar y padear strings correctamente
     */
    private function truncateAndPad($value, $length, $padChar = ' ')
    {
        if (empty($value)) {
            return null;
        }
        return str_pad(substr($value, 0, $length), $length, $padChar, STR_PAD_RIGHT);
    }

    private function sanitizeNullableString($value, $length = null)
    {
        if (is_null($value)) {
            return null;
        }

        $sanitized = trim($value);

        if ($sanitized === '') {
            return null;
        }

        if (!is_null($length)) {
            return mb_substr($sanitized, 0, $length);
        }

        return $sanitized;
    }

    private function parseDecimalValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace(',', '.', $value);
            if (is_numeric($normalized)) {
                return (float) $normalized;
            }

            if (preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches)) {
                return (float) $matches[0];
            }
        }

        return null;
    }
    public function index(Request $request)
    {
        // Obtener clientes para el filtro
        $clientes = Clientes::where('cli_estado', true)
            ->orderBy('cli_razonsocial')
            ->get();
        
        // Construir query con filtros
        $query = Ventas::query();
        
        // Filtro por cliente
        if ($request->filled('cliente')) {
            $query->where('coti_codigocli', 'LIKE', $request->cliente . '%');
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('coti_estado', 'LIKE', $request->estado . '%');
        }
        
        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('coti_fechaalta', '>=', $request->fecha_desde);
        }
        
        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('coti_fechaalta', '<=', $request->fecha_hasta);
        }
        
        // Ordenar y paginar
        $cotizaciones = $query->orderBy('coti_num', 'desc')
            ->paginate(20)
            ->withQueryString(); // Mantener filtros en la paginación

        return View::make('ventas.index', compact('cotizaciones', 'clientes'));
    }

    public function create() 
    {
        // Cargar datos para los selectores
        try {
            $matrices = Matriz::orderBy('matriz_descripcion')->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando matrices:', ['error' => $e->getMessage()]);
            $matrices = collect();
        }

        try {
            $sectores = Divis::orderBy('divis_descripcion')->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando sectores:', ['error' => $e->getMessage()]);
            $sectores = collect();
        }

        try {
            $condicionesPago = CondicionPago::where('pag_estado', true)
                ->orderBy('pag_descripcion')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando condiciones de pago:', ['error' => $e->getMessage()]);
            $condicionesPago = collect();
        }

        try {
            $sectoresCliente = Divis::where('divis_lab', true)
                ->orderBy('divis_descripcion')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando sectores de cliente:', ['error' => $e->getMessage()]);
            $sectoresCliente = collect();
        }

        try {
            $listasPrecios = ListaPrecio::where('lp_estado', true)
                ->orderBy('lp_descripcion')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando listas de precios:', ['error' => $e->getMessage()]);
            $listasPrecios = collect([
                (object)['lp_codigo' => 'UNO  ', 'lp_descripcion' => 'Lista Principal'],
                (object)['lp_codigo' => 'DOS  ', 'lp_descripcion' => 'Lista Secundaria'],
            ]);
        }

        $cotizacionConfig = [
            'modo' => 'create',
            'puedeEditar' => true,
            'ensayosIniciales' => [],
            'componentesIniciales' => [],
        ];

        return View::make('ventas.create', compact(
            'matrices',
            'sectores',
            'condicionesPago',
            'listasPrecios',
            'cotizacionConfig',
            'sectoresCliente'
        ));
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== INICIO CREACIÓN DE COTIZACIÓN ===');
            Log::info('Datos recibidos en store:', $request->all());

            // Validar datos básicos
            Log::info('Iniciando validación de datos básicos');
            Log::info('Campos para validación:', [
                'coti_codigocli' => $request->coti_codigocli,
                'coti_fechaalta' => $request->coti_fechaalta,
            ]);
            
            $request->validate([
                'coti_codigocli' => 'required|string',
                'coti_fechaalta' => 'required|date',
            ], [
                'coti_codigocli.required' => 'El código de cliente es obligatorio',
                'coti_fechaalta.required' => 'La fecha de alta es obligatoria',
            ]);
            
            Log::info('Validación completada exitosamente');

            // Generar número de cotización automático
            Log::info('Generando número de cotización');
            $ultimaCotizacion = Ventas::orderBy('coti_num', 'desc')->first();
            $nuevoNumero = $ultimaCotizacion ? intval($ultimaCotizacion->coti_num) + 1 : 1;
            Log::info('Número de cotización generado:', ['numero' => $nuevoNumero]);

            // Crear la cotización
            Log::info('Creando instancia de cotización');
            $cotizacion = new Ventas();

            // Obtener datos del cliente
            Log::info('Obteniendo datos del cliente');
            $codigoCliente = $this->truncateAndPad($request->coti_codigocli, 10);
            $cliente = Clientes::where('cli_codigo', $codigoCliente)
                ->where('cli_estado', true)
                ->first();
                
            if (!$cliente) {
                Log::error('Cliente no encontrado:', ['codigo' => $request->coti_codigocli]);
                throw new \Exception('Cliente no encontrado con código: ' . $request->coti_codigocli);
            }
            
            Log::info('Cliente encontrado:', [
                'codigo' => trim($cliente->cli_codigo),
                'razon_social' => trim($cliente->cli_razonsocial),
                'condicion_pago' => $cliente->cli_codigopag ? trim($cliente->cli_codigopag) : null,
                'lista_precios' => $cliente->cli_codigolp ? trim($cliente->cli_codigolp) : null,
            ]);

            // Campos principales
            Log::info('Asignando campos principales');
            $cotizacion->coti_num = $nuevoNumero;
            $cotizacion->coti_para = $this->sanitizeNullableString($request->coti_para, null);
            $cotizacion->coti_descripcion = $request->coti_descripcion;
            $cotizacion->coti_codigocli = $codigoCliente;
            $cotizacion->coti_fechaalta = $request->coti_fechaalta ?: now()->format('Y-m-d');
            $cotizacion->coti_fechafin = $request->coti_fechafin;
            // Sector (se valida más adelante contra divis)
            $cotizacion->coti_sector = null;
            // Mapear estados del formulario a códigos de BD
            $estadosMap = [
                'En Espera' => 'E    ',
                'Aprobado' => 'A    ',
                'Rechazado' => 'R    ',
                'En Proceso' => 'P    ',
            ];
            
            $estadoFormulario = $request->coti_estado ?: 'En Espera';
            $cotizacion->coti_estado = $estadosMap[$estadoFormulario] ?? 'E    ';
            
            Log::info('Estado mapeado:', [
                'estado_formulario' => $estadoFormulario,
                'estado_bd' => $cotizacion->coti_estado
            ]);
            // Campo coti_vigencia eliminado - no existe en la tabla

            // Campos de gestión
            Log::info('Asignando campos de gestión');
            $cotizacion->coti_responsable = $this->truncateAndPad($request->coti_responsable, 20);
            $cotizacion->coti_aprobo = $this->truncateAndPad($request->coti_aprobo, 20);
            $cotizacion->coti_fechaaprobado = $request->coti_fechaaprobado;
            $cotizacion->coti_fechaencurso = $request->coti_fechaencurso;
            $cotizacion->coti_fechaaltatecnica = $request->coti_fechaaltatecnica;

            // Campos técnicos - Solo asignar campos que existen en la tabla
            Log::info('Asignando campos técnicos');
            Log::info('Valores técnicos recibidos:', [
                'coti_codigomatriz' => $request->coti_codigomatriz,
            ]);
            
            // Solo asignar coti_codigomatriz que sí existe en la tabla
            $cotizacion->coti_codigomatriz = $this->truncateAndPad($request->coti_codigomatriz, 15);
            
            Log::info('Valores técnicos asignados:', [
                'coti_codigomatriz' => "'" . $cotizacion->coti_codigomatriz . "'",
            ]);

            // Campos de empresa/cliente (usar datos del cliente como base)
            Log::info('=== ASIGNANDO CAMPOS DE EMPRESA ===');
            Log::info('Datos de empresa recibidos:', [
                'coti_empresa' => $request->coti_empresa,
                'coti_establecimiento' => $request->coti_establecimiento,
                'coti_contacto' => $request->coti_contacto,
                'coti_direccioncli' => $request->coti_direccioncli,
                'coti_localidad' => $request->coti_localidad,
                'coti_partido' => $request->coti_partido,
                'coti_cuit' => $request->coti_cuit,
                'coti_codigopostal' => $request->coti_codigopostal,
                'coti_telefono' => $request->coti_telefono,
            ]);
            
            // Usar datos del formulario si están presentes, sino usar datos del cliente
            $cotizacion->coti_empresa = $request->coti_empresa ?: trim($cliente->cli_razonsocial);
            $cotizacion->coti_establecimiento = $request->coti_establecimiento;
            $cotizacion->coti_contacto = $this->sanitizeNullableString(
                $request->coti_contacto,
                120
            ) ?: ($cliente->cli_contacto ? trim($cliente->cli_contacto) : null);
            $cotizacion->coti_direccioncli = $request->coti_direccioncli ?: 
                ($cliente->cli_direccion ? trim($cliente->cli_direccion) : null);
            $cotizacion->coti_localidad = $request->coti_localidad ?: 
                ($cliente->cli_localidad ? trim($cliente->cli_localidad) : null);
            $cotizacion->coti_partido = $request->coti_partido;
            $cotizacion->coti_cuit = $request->coti_cuit ?: $cliente->cli_cuit;
            $cotizacion->coti_codigopostal = $request->coti_codigopostal ?: 
                ($cliente->cli_codigopostal ? trim($cliente->cli_codigopostal) : null);
            $cotizacion->coti_telefono = $request->coti_telefono ?: $cliente->cli_telefono;
            $cotizacion->coti_mail1 = $this->sanitizeNullableString(
                $request->coti_mail1,
                120
            ) ?: ($cliente->cli_email ? trim($cliente->cli_email) : null);
            $sectorFormulario = $this->sanitizeNullableString($request->coti_sector, 4);
            $sectorCliente = $this->sanitizeNullableString($cliente->cli_codigocrub ?? null, 4);

            $sectorCandidato = null;
            if ($sectorFormulario) {
                $sectorFormulario = $this->truncateAndPad($sectorFormulario, 4);
                if (Divis::where('divis_codigo', $sectorFormulario)->exists()) {
                    $sectorCandidato = $sectorFormulario;
                }
            }

            if (!$sectorCandidato && $sectorCliente) {
                $sectorCliente = $this->truncateAndPad($sectorCliente, 4);
                if (Divis::where('divis_codigo', $sectorCliente)->exists()) {
                    $sectorCandidato = $sectorCliente;
                }
            }

            $cotizacion->coti_sector = $sectorCandidato;
            
            Log::info('Campos de empresa asignados (con datos del cliente):', [
                'coti_empresa' => $cotizacion->coti_empresa,
                'coti_direccioncli' => $cotizacion->coti_direccioncli,
                'coti_localidad' => $cotizacion->coti_localidad,
                'coti_cuit' => $cotizacion->coti_cuit,
            ]);

            // Campos adicionales - Solo campos que existen en la tabla
            Log::info('Asignando campos adicionales');
            $cotizacion->coti_referencia_tipo = $this->truncateAndPad($request->coti_referencia_tipo, 30);
            $cotizacion->coti_referencia_valor = $this->sanitizeNullableString($request->coti_referencia_valor, 120);
            $cotizacion->coti_oc_referencia = $this->sanitizeNullableString($request->coti_oc_referencia, 120);
            $cotizacion->coti_hes_has_tipo = $this->truncateAndPad($request->coti_hes_has_tipo, 10);
            $cotizacion->coti_hes_has_valor = $this->sanitizeNullableString($request->coti_hes_has_valor, 120);
            $cotizacion->coti_gr_contrato_tipo = $this->truncateAndPad($request->coti_gr_contrato_tipo, 30);
            $cotizacion->coti_gr_contrato = $this->sanitizeNullableString($request->coti_gr_contrato, 120);
            $cotizacion->coti_otro_referencia = $this->sanitizeNullableString($request->coti_otro_referencia, 120);
            $cotizacion->coti_notas = $request->coti_notas;
            $cotizacion->coti_codigosuc = $this->truncateAndPad($request->coti_codigosuc, 10);
            
            // Campos de descuentos
            $cotizacion->coti_descuentoglobal = $request->filled('descuento') ? floatval($request->descuento) : 0.00;
            $cotizacion->coti_sector_laboratorio_pct = $request->filled('sector_laboratorio_porcentaje') ? floatval($request->sector_laboratorio_porcentaje) : 0.00;
            $cotizacion->coti_sector_higiene_pct = $request->filled('sector_higiene_porcentaje') ? floatval($request->sector_higiene_porcentaje) : 0.00;
            $cotizacion->coti_sector_microbiologia_pct = $request->filled('sector_microbiologia_porcentaje') ? floatval($request->sector_microbiologia_porcentaje) : 0.00;
            $cotizacion->coti_sector_cromatografia_pct = $request->filled('sector_cromatografia_porcentaje') ? floatval($request->sector_cromatografia_porcentaje) : 0.00;
            $cotizacion->coti_sector_laboratorio_contacto = $this->sanitizeNullableString($request->sector_laboratorio_contacto, 100);
            $cotizacion->coti_sector_higiene_contacto = $this->sanitizeNullableString($request->sector_higiene_contacto, 100);
            $cotizacion->coti_sector_microbiologia_contacto = $this->sanitizeNullableString($request->sector_microbiologia_contacto, 100);
            $cotizacion->coti_sector_cromatografia_contacto = $this->sanitizeNullableString($request->sector_cromatografia_contacto, 100);
            $cotizacion->coti_sector_laboratorio_observaciones = $this->sanitizeNullableString($request->sector_laboratorio_observaciones);
            $cotizacion->coti_sector_higiene_observaciones = $this->sanitizeNullableString($request->sector_higiene_observaciones);
            $cotizacion->coti_sector_microbiologia_observaciones = $this->sanitizeNullableString($request->sector_microbiologia_observaciones);
            $cotizacion->coti_sector_cromatografia_observaciones = $this->sanitizeNullableString($request->sector_cromatografia_observaciones);
            
            // Campos financieros eliminados - no existen en la tabla real
            Log::info('=== CAMPOS FINANCIEROS OMITIDOS ===');
            Log::info('Los siguientes campos no existen en la tabla: coti_abono, coti_importe, coti_usos, coti_codigopag, coti_codigolp, coti_nroprecio');

            Log::info('=== PREPARANDO PARA GUARDAR COTIZACIÓN ===');
            Log::info('Datos finales de la cotización antes de save:', [
                'coti_num' => $cotizacion->coti_num . ' (tipo: ' . gettype($cotizacion->coti_num) . ')',
                'coti_descripcion' => $cotizacion->coti_descripcion,
                'coti_codigocli' => "'" . $cotizacion->coti_codigocli . "'",
                'coti_fechaalta' => $cotizacion->coti_fechaalta,
                'coti_estado' => "'" . $cotizacion->coti_estado . "'",
                'coti_codigomatriz' => $cotizacion->coti_codigomatriz ? "'" . $cotizacion->coti_codigomatriz . "'" : 'NULL',
                'coti_empresa' => $cotizacion->coti_empresa,
                'coti_direccioncli' => $cotizacion->coti_direccioncli,
                'coti_localidad' => $cotizacion->coti_localidad,
                'coti_cuit' => $cotizacion->coti_cuit,
                'coti_codigosuc' => $cotizacion->coti_codigosuc ? "'" . $cotizacion->coti_codigosuc . "'" : 'NULL'
            ]);

            // Verificar que todos los campos requeridos estén presentes
            Log::info('Verificando campos requeridos:');
            if (!$cotizacion->coti_num) {
                Log::error('ERROR: coti_num está vacío');
            }
            if (!$cotizacion->coti_codigocli) {
                Log::error('ERROR: coti_codigocli está vacío');
            }
            if (!$cotizacion->coti_fechaalta) {
                Log::error('ERROR: coti_fechaalta está vacío');
            }

            Log::info('Ejecutando save()...');
            try {
                $result = $cotizacion->save();
                Log::info('Save() ejecutado exitosamente', ['result' => $result]);
                Log::info('ID de cotización guardada:', ['coti_num' => $cotizacion->coti_num]);
            } catch (\Exception $saveException) {
                Log::error('ERROR EN SAVE():', [
                    'message' => $saveException->getMessage(),
                    'file' => $saveException->getFile(),
                    'line' => $saveException->getLine(),
                    'trace' => $saveException->getTraceAsString()
                ]);
                throw $saveException;
            }

            Log::info('Cotización creada exitosamente', ['numero' => $cotizacion->coti_num]);
            
            // Procesar ensayos y componentes
            $this->procesarEnsayosYComponentes($request, $cotizacion->coti_num);
            
            Log::info('=== FIN CREACIÓN DE COTIZACIÓN EXITOSA ===');

            return redirect()->route('ventas.index')
                ->with('success', 'Cotización creada exitosamente con número: ' . $cotizacion->coti_num);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('=== ERROR DE VALIDACIÓN EN COTIZACIÓN ===', [
                'errors' => $e->validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('=== ERROR AL CREAR COTIZACIÓN ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        
        $cotizacion = Ventas::find($id);
        if (!$cotizacion) {
            abort(404, 'Cotización not found');
        }
        
        // Cargar datos para los selectores
        try {
            $matrices = Matriz::orderBy('matriz_descripcion')->get();
        } catch (\Exception $e) {
            Log::warning('Error cargando matrices:', ['error' => $e->getMessage()]);
            $matrices = collect();
        }
        
        // Cargar items de la cotización (ensayos y componentes) con relaciones
        $ensayos = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
            ->where('cotio_subitem', 0)
            ->orderBy('cotio_item')
            ->get();
        
        // Cargar componentes con el método (cotio_codigometodo apunta a tabla metodo)
        $componentes = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
            ->where('cotio_subitem', '>', 0)
            ->orderBy('cotio_item')
            ->orderBy('cotio_subitem')
            ->get();

        // Ahora siempre permitimos editar la cotización, incluso si está aprobada.
        $puedeEditar = true;

        $agrupadoresCatalogo = CotioItems::muestras()
            ->with('componentesAsociados')
            ->get()
            ->keyBy(function ($item) {
                return Str::lower(trim($item->cotio_descripcion));
            });

        $ensayosIniciales = $ensayos->map(function ($ensayo) use ($componentes, $agrupadoresCatalogo) {
            $cantidad = $ensayo->cotio_cantidad ?? 1;
            $componentesDelEnsayo = $componentes->where('cotio_item', $ensayo->cotio_item);
            $precioUnitario = $componentesDelEnsayo->sum(function ($comp) {
                $precio = $comp->cotio_precio ?? 0;
                $cantidad = $comp->cotio_cantidad ?? 1;
                return $precio * $cantidad;
            });

            $descripcionClave = Str::lower(trim($ensayo->cotio_descripcion ?? ''));
            $agrupador = $agrupadoresCatalogo->get($descripcionClave);

            return [
                'item' => (int) $ensayo->cotio_item,
                'muestra_id' => $agrupador?->id,
                'descripcion' => $ensayo->cotio_descripcion,
                'codigo' => $agrupador ? str_pad($agrupador->id, 15, '0', STR_PAD_LEFT) : ($ensayo->cotio_codigoprod ?? ''),
                'cantidad' => (float) $cantidad,
                'precio' => (float) $precioUnitario,
                'total' => (float) ($precioUnitario * $cantidad),
                'tipo' => 'ensayo',
                'componentes_sugeridos' => $agrupador ? $agrupador->componentesAsociados->pluck('id')->values()->all() : [],
            ];
        })->values();

        $componentesIniciales = [];
        $contadorComponentes = 0;
        $maxItemEnsayo = (int) ($ensayos->max('cotio_item') ?? 0);
        foreach ($componentes as $componente) {
            $contadorComponentes++;
            $metodoTexto = '-';

            if ($componente->cotio_codigometodo) {
                $metodoCodigo = trim($componente->cotio_codigometodo);
                $metodo = Metodo::where('metodo_codigo', $metodoCodigo)->first();
                $metodoTexto = $metodo
                    ? $metodo->metodo_codigo . ' - ' . ($metodo->metodo_descripcion ?? '')
                    : $metodoCodigo;
            } elseif ($componente->cotio_codigometodo_analisis) {
                $metodoCodigo = trim($componente->cotio_codigometodo_analisis);
                $metodoAnalisis = MetodoAnalisis::where('codigo', $metodoCodigo)->first();
                $metodoTexto = $metodoAnalisis
                    ? $metodoAnalisis->codigo . ' - ' . ($metodoAnalisis->nombre ?? $metodoAnalisis->descripcion ?? '')
                    : $metodoCodigo;
            }

            $componentesIniciales[] = [
                'item' => $maxItemEnsayo + $contadorComponentes,
                'analisis_id' => null,
                'descripcion' => $componente->cotio_descripcion,
                'codigo' => $componente->cotio_codigoprod ?? '',
                'cantidad' => (float) ($componente->cotio_cantidad ?? 1),
                'precio' => (float) ($componente->cotio_precio ?? 0),
                'total' => (float) (($componente->cotio_precio ?? 0) * ($componente->cotio_cantidad ?? 1)),
                'tipo' => 'componente',
                'ensayo_asociado' => (int) $componente->cotio_item,
                'metodo_analisis_id' => $componente->cotio_codigometodo_analisis ? trim($componente->cotio_codigometodo_analisis) : null,
                'metodo_codigo' => $componente->cotio_codigometodo ? trim($componente->cotio_codigometodo) : null,
                'metodo_descripcion' => $metodoTexto,
                'unidad_medida' => $componente->cotio_codigoum ? trim($componente->cotio_codigoum) : null,
                'limite_deteccion' => $componente->limite_deteccion ?? null,
                'ley_normativa_id' => null,
            ];
        }

        $cotizacionConfig = [
            'modo' => 'edit',
            'puedeEditar' => $puedeEditar,
            'ensayosIniciales' => $ensayosIniciales,
            'componentesIniciales' => $componentesIniciales,
        ];
        
        $descuentoCliente = $this->calcularDescuentoCotizacion($cotizacion);
        
        // Prioridad: primero descuentos de la cotización, luego del cliente
        $descuentoGlobalCliente = 0.0;
        if (isset($cotizacion->coti_descuentoglobal) && $cotizacion->coti_descuentoglobal > 0) {
            $descuentoGlobalCliente = (float) $cotizacion->coti_descuentoglobal;
        } elseif ($cotizacion->cliente) {
            $descuentoGlobalCliente = (float) ($cotizacion->cliente->cli_descuentoglobal ?? 0);
        }
        
        $sectorCodigoOriginal = $cotizacion->coti_sector ?? optional($cotizacion->cliente)->cli_codigocrub;
        $sectorCodigo = $this->normalizarCodigoSector($sectorCodigoOriginal);
        $descuentoSectorAplicado = 0.0;
        if ($sectorCodigo) {
            $descuentoSectorAplicado = $this->obtenerDescuentoSectorCotizacion($cotizacion, $sectorCodigo);
        }
        if ($descuentoSectorAplicado == 0.0 && $cotizacion->cliente) {
            $descuentoSectorAplicado = $this->obtenerDescuentoSector($cotizacion->cliente, $sectorCodigo);
        }
        
        $sectorEtiqueta = trim(optional($cotizacion->sector)->divis_descripcion ?? $cotizacion->coti_sector ?? '');

        return View::make('ventas.edit', compact(
            'cotizacion',
            'matrices',
            'ensayos',
            'componentes',
            'puedeEditar',
            'cotizacionConfig',
            'descuentoCliente',
            'descuentoGlobalCliente',
            'descuentoSectorAplicado',
            'sectorEtiqueta'
        ));
    }
    
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        
        try {
            $cotizacion = Ventas::find($id);
            if (!$cotizacion) {
                return redirect()->route('ventas.index', request()->query())
                    ->with('error', 'Cotización no encontrada');
            }
            
            $cotizacion->delete();
            
            // Preservar los filtros activos en la redirección
            return redirect()->route('ventas.index', request()->query())
                ->with('success', 'Cotización eliminada exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar cotización:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('ventas.index', request()->query())
                ->with('error', 'Error al eliminar la cotización');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $cotizacion = Ventas::find($id);
            
            if (!$cotizacion) {
                return redirect()->route('ventas.index')
                    ->with('error', 'Cotización no encontrada');
            }
            
            // Actualizar campos principales
            $cotizacion->coti_descripcion = $request->coti_descripcion;
            $cotizacion->coti_para = $this->sanitizeNullableString($request->coti_para, null);
            $cotizacion->coti_codigocli = $this->truncateAndPad($request->coti_codigocli, 10);
            $cotizacion->coti_fechaalta = $request->coti_fechaalta;
            $cotizacion->coti_fechafin = $request->coti_fechafin;
            
            // Mapear estado
            $estadosMap = [
                'E' => 'E    ',
                'A' => 'A    ',
                'R' => 'R    ',
                'P' => 'P    ',
            ];
            
            $estadoFormulario = $request->coti_estado ?: 'E';
            $cotizacion->coti_estado = $estadosMap[$estadoFormulario] ?? 'E    ';
            
            // Campos técnicos
            $cotizacion->coti_codigomatriz = $this->truncateAndPad($request->coti_codigomatriz, 15);
            $cotizacion->coti_codigosuc = $this->truncateAndPad($request->coti_codigosuc, 10);
            
            // Campos de gestión
            $cotizacion->coti_responsable = $this->truncateAndPad($request->coti_responsable, 20);
            $cotizacion->coti_aprobo = $this->truncateAndPad($request->coti_aprobo, 20);
            $cotizacion->coti_fechaaprobado = $request->coti_fechaaprobado;
            $cotizacion->coti_fechaencurso = $request->coti_fechaencurso;
            $cotizacion->coti_fechaaltatecnica = $request->coti_fechaaltatecnica;
            
            // Campos de empresa
            $cotizacion->coti_empresa = $request->coti_empresa;
            $cotizacion->coti_establecimiento = $request->coti_establecimiento;
            $cotizacion->coti_contacto = $this->sanitizeNullableString($request->coti_contacto, 120);
            $cotizacion->coti_direccioncli = $request->coti_direccioncli;
            $cotizacion->coti_localidad = $request->coti_localidad;
            $cotizacion->coti_partido = $request->coti_partido;
            $cotizacion->coti_cuit = $request->coti_cuit;
            $cotizacion->coti_codigopostal = $request->coti_codigopostal;
            $cotizacion->coti_telefono = $request->coti_telefono;
            $cotizacion->coti_mail1 = $this->sanitizeNullableString($request->coti_mail1, 120);
            $cotizacion->coti_sector = $this->sanitizeNullableString($request->coti_sector, 60);
            
            $cotizacion->coti_referencia_tipo = $this->truncateAndPad($request->coti_referencia_tipo, 30);
            $cotizacion->coti_referencia_valor = $this->sanitizeNullableString($request->coti_referencia_valor, 120);
            $cotizacion->coti_oc_referencia = $this->sanitizeNullableString($request->coti_oc_referencia, 120);
            $cotizacion->coti_hes_has_tipo = $this->truncateAndPad($request->coti_hes_has_tipo, 10);
            $cotizacion->coti_hes_has_valor = $this->sanitizeNullableString($request->coti_hes_has_valor, 120);
            $cotizacion->coti_gr_contrato_tipo = $this->truncateAndPad($request->coti_gr_contrato_tipo, 30);
            $cotizacion->coti_gr_contrato = $this->sanitizeNullableString($request->coti_gr_contrato, 120);
            $cotizacion->coti_otro_referencia = $this->sanitizeNullableString($request->coti_otro_referencia, 120);
            
            // Notas
            $cotizacion->coti_notas = $request->coti_notas;
            
            // Campos de descuentos
            $cotizacion->coti_descuentoglobal = $request->filled('descuento') ? floatval($request->descuento) : 0.00;
            $cotizacion->coti_sector_laboratorio_pct = $request->filled('sector_laboratorio_porcentaje') ? floatval($request->sector_laboratorio_porcentaje) : 0.00;
            $cotizacion->coti_sector_higiene_pct = $request->filled('sector_higiene_porcentaje') ? floatval($request->sector_higiene_porcentaje) : 0.00;
            $cotizacion->coti_sector_microbiologia_pct = $request->filled('sector_microbiologia_porcentaje') ? floatval($request->sector_microbiologia_porcentaje) : 0.00;
            $cotizacion->coti_sector_cromatografia_pct = $request->filled('sector_cromatografia_porcentaje') ? floatval($request->sector_cromatografia_porcentaje) : 0.00;
            $cotizacion->coti_sector_laboratorio_contacto = $this->sanitizeNullableString($request->sector_laboratorio_contacto, 100);
            $cotizacion->coti_sector_higiene_contacto = $this->sanitizeNullableString($request->sector_higiene_contacto, 100);
            $cotizacion->coti_sector_microbiologia_contacto = $this->sanitizeNullableString($request->sector_microbiologia_contacto, 100);
            $cotizacion->coti_sector_cromatografia_contacto = $this->sanitizeNullableString($request->sector_cromatografia_contacto, 100);
            $cotizacion->coti_sector_laboratorio_observaciones = $this->sanitizeNullableString($request->sector_laboratorio_observaciones);
            $cotizacion->coti_sector_higiene_observaciones = $this->sanitizeNullableString($request->sector_higiene_observaciones);
            $cotizacion->coti_sector_microbiologia_observaciones = $this->sanitizeNullableString($request->sector_microbiologia_observaciones);
            $cotizacion->coti_sector_cromatografia_observaciones = $this->sanitizeNullableString($request->sector_cromatografia_observaciones);

            // Versionado simple: cada vez que se actualiza, incrementamos la versión.
            // Si no existe (migración recién aplicada), asumimos versión 1 y luego sumamos.
            $cotizacion->coti_version = (int)($cotizacion->coti_version ?? 1) + 1;
            
            $cotizacion->save();

            $this->procesarEnsayosYComponentes($request, $cotizacion->coti_num, true);
            
            return redirect()->route('ventas.index')
                ->with('success', 'Cotización actualizada exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar cotización:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('ventas.index')
                ->with('error', 'Error al actualizar la cotización: ' . $e->getMessage());
        }
    }

    public function imprimir($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }

        $cotizacion = Ventas::with([
                'cliente.condicionPago',
                'matriz',
                'condicionPago',
                'listaPrecio',
            ])->find($id);

        if (!$cotizacion) {
            abort(404, 'Cotización not found');
        }

        $ensayos = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
            ->where('cotio_subitem', 0)
            ->orderBy('cotio_item')
            ->get();

        $componentes = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
            ->where('cotio_subitem', '>', 0)
            ->with(['metodoAnalisis', 'metodoMuestreo'])
            ->orderBy('cotio_item')
            ->orderBy('cotio_subitem')
            ->get();

        $componentesAgrupados = $componentes->groupBy(function ($componente) {
            return (int) $componente->cotio_item;
        });

        $items = $ensayos->map(function ($ensayo) use ($componentesAgrupados) {
            $componentesEnsayo = $componentesAgrupados->get((int) $ensayo->cotio_item, collect());

            if (!$componentesEnsayo instanceof \Illuminate\Support\Collection) {
                $componentesEnsayo = collect($componentesEnsayo);
            }

            $cantidadEnsayo = $this->parseDecimalValue($ensayo->cotio_cantidad ?? 1) ?? 1;
            if ($cantidadEnsayo <= 0) {
                $cantidadEnsayo = 1;
            }

            $subtotalComponentes = $componentesEnsayo->sum(function ($componente) {
                $precio = $this->parseDecimalValue($componente->cotio_precio ?? 0) ?? 0;
                $cantidad = $this->parseDecimalValue($componente->cotio_cantidad ?? 1) ?? 1;

                if ($cantidad <= 0) {
                    $cantidad = 1;
                }

                return $precio * $cantidad;
            });

            $precioUnitario = $subtotalComponentes;
            $total = $precioUnitario * $cantidadEnsayo;

            return [
                'item' => (int) $ensayo->cotio_item,
                'descripcion' => trim($ensayo->cotio_descripcion ?? 'Sin descripción'),
                'cantidad' => $cantidadEnsayo,
                'precio_unitario' => $precioUnitario,
                'total' => $total,
                'componentes' => $componentesEnsayo->map(function ($componente) {
                    $precio = $this->parseDecimalValue($componente->cotio_precio ?? 0) ?? 0;
                    $cantidad = $this->parseDecimalValue($componente->cotio_cantidad ?? 1) ?? 1;

                    if ($cantidad <= 0) {
                        $cantidad = 1;
                    }

                    // Obtener nombre del método
                    $metodoCodigo = trim($componente->cotio_codigometodo_analisis ?? $componente->cotio_codigometodo ?? '');
                    $metodoNombre = '';
                    
                    if ($metodoCodigo) {
                        // Intentar desde MetodoAnalisis
                        if ($componente->metodoAnalisis) {
                            $metodoNombre = trim($componente->metodoAnalisis->nombre ?? '');
                        }
                        // Si no, intentar desde MetodoMuestreo
                        if (!$metodoNombre && $componente->metodoMuestreo) {
                            $metodoNombre = trim($componente->metodoMuestreo->nombre ?? '');
                        }
                        // Si no, buscar en tabla metodo (legacy)
                        if (!$metodoNombre) {
                            $metodo = Metodo::where('metodo_codigo', $metodoCodigo)->first();
                            if ($metodo) {
                                $metodoNombre = trim($metodo->metodo_descripcion ?? '');
                            }
                        }
                    }

                    return [
                        'descripcion' => trim($componente->cotio_descripcion ?? ''),
                        'metodo' => $metodoNombre ?: $metodoCodigo,
                        'metodo_codigo' => $metodoCodigo,
                        'unidad' => trim($componente->cotio_codigoum ?? ''),
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'total' => $precio * $cantidad,
                    ];
                })->values(),
            ];
        })->sortBy('item')->values();

        $ensayoItems = $ensayos->pluck('cotio_item')->map(function ($item) {
            return (int) $item;
        });

        $componentesSueltos = $componentes->filter(function ($componente) use ($ensayoItems) {
            return !$ensayoItems->contains((int) $componente->cotio_item);
        })->map(function ($componente) {
            $precio = $this->parseDecimalValue($componente->cotio_precio ?? 0) ?? 0;
            $cantidad = $this->parseDecimalValue($componente->cotio_cantidad ?? 1) ?? 1;

            if ($cantidad <= 0) {
                $cantidad = 1;
            }

            // Obtener nombre del método
            $metodoCodigo = trim($componente->cotio_codigometodo_analisis ?? $componente->cotio_codigometodo ?? '');
            $metodoNombre = '';
            
            if ($metodoCodigo) {
                // Intentar desde MetodoAnalisis
                if ($componente->metodoAnalisis) {
                    $metodoNombre = trim($componente->metodoAnalisis->nombre ?? '');
                }
                // Si no, intentar desde MetodoMuestreo
                if (!$metodoNombre && $componente->metodoMuestreo) {
                    $metodoNombre = trim($componente->metodoMuestreo->nombre ?? '');
                }
                // Si no, buscar en tabla metodo (legacy)
                if (!$metodoNombre) {
                    $metodo = Metodo::where('metodo_codigo', $metodoCodigo)->first();
                    if ($metodo) {
                        $metodoNombre = trim($metodo->metodo_descripcion ?? '');
                    }
                }
            }

            return [
                'descripcion' => trim($componente->cotio_descripcion ?? ''),
                'metodo' => $metodoNombre ?: $metodoCodigo,
                'metodo_codigo' => $metodoCodigo,
                'unidad' => trim($componente->cotio_codigoum ?? ''),
                'cantidad' => $cantidad,
                'precio' => $precio,
                'total' => $precio * $cantidad,
            ];
        })->values();

        $subtotalItems = $items->sum(function ($item) {
            return $item['total'];
        });

        $totalComponentesSueltos = $componentesSueltos->sum(function ($componente) {
            return $componente['total'];
        });

        $subtotal = $subtotalItems + $totalComponentesSueltos;

        $cliente = $cotizacion->cliente;
        $descuentoPorcentaje = max($this->calcularDescuentoCotizacion($cotizacion), 0);
        $descuentoMonto = $subtotal * ($descuentoPorcentaje / 100);
        $totalConDescuento = $subtotal - $descuentoMonto;

        $totalMuestras = $ensayos->sum(function ($ensayo) {
            $cantidad = $this->parseDecimalValue($ensayo->cotio_cantidad ?? 1) ?? 1;
            return $cantidad > 0 ? $cantidad : 1;
        });

        $condicionPago = optional($cotizacion->condicionPago)->pag_descripcion
            ?? optional($cliente?->condicionPago)->pag_descripcion
            ?? 'Contra entrega';

        // Verificar si hay empresas relacionadas
        $tieneEmpresaRelacionada = false;
        $empresaRelacionada = null;
        
        if ($cliente) {
            $razonSocial = trim((string) ($cliente->cli_rel_empresa_razon_social ?? ''));
            if (!empty($razonSocial)) {
                $tieneEmpresaRelacionada = true;
                $empresaRelacionada = [
                    'razon_social' => $razonSocial,
                    'cuit' => trim((string) ($cliente->cli_rel_empresa_cuit ?? '')),
                    'direcciones' => trim((string) ($cliente->cli_rel_empresa_direcciones ?? '')),
                    'localidad' => trim((string) ($cliente->cli_rel_empresa_localidad ?? '')),
                    'partido' => trim((string) ($cliente->cli_rel_empresa_partido ?? '')),
                    'contacto' => trim((string) ($cliente->cli_rel_empresa_contacto ?? '')),
                ];
            }
        }

        $data = [
            'cotizacion' => $cotizacion,
            'items' => $items,
            'componentesSueltos' => $componentesSueltos,
            'totales' => [
                'subtotal_items' => $subtotalItems,
                'subtotal_componentes' => $totalComponentesSueltos,
                'subtotal' => $subtotal,
                'descuento_porcentaje' => $descuentoPorcentaje,
                'descuento_monto' => $descuentoMonto,
                'total' => $totalConDescuento,
                'total_muestras' => $totalMuestras,
            ],
            'condicionPagoDescripcion' => $condicionPago,
            'fechaActual' => Carbon::now(),
            'tieneEmpresaRelacionada' => $tieneEmpresaRelacionada,
            'empresaRelacionada' => $empresaRelacionada,
        ];

        $pdf = Pdf::loadView('ventas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Cotizacion_' . trim((string) $cotizacion->coti_num) . '.pdf';

        return $pdf->stream($fileName);
    }

    // API para buscar clientes
    public function buscarClientes(Request $request)
    {
        $termino = $request->get('q', '');
        
        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        try {
            $clientes = Clientes::where('cli_estado', true)
                ->where(function($query) use ($termino) {
                    $query->where('cli_codigo', 'ILIKE', "%{$termino}%")
                          ->orWhere('cli_razonsocial', 'ILIKE', "%{$termino}%")
                          ->orWhere('cli_fantasia', 'ILIKE', "%{$termino}%");
                })
                ->limit(10)
                ->get()
                ->map(function($cliente) {
                    return [
                        'id' => trim($cliente->cli_codigo),
                        'codigo' => trim($cliente->cli_codigo),
                        'text' => trim($cliente->cli_codigo) . ' - ' . trim($cliente->cli_razonsocial),
                        'razon_social' => trim($cliente->cli_razonsocial),
                        'fantasia' => $cliente->cli_fantasia ? trim($cliente->cli_fantasia) : null,
                        'direccion' => $cliente->cli_direccion ? trim($cliente->cli_direccion) : null,
                        'localidad' => $cliente->cli_localidad ? trim($cliente->cli_localidad) : null,
                        'cuit' => $cliente->cli_cuit,
                        'codigo_postal' => $cliente->cli_codigopostal ? trim($cliente->cli_codigopostal) : null,
                        'telefono' => $cliente->cli_telefono,
                        'email' => $cliente->cli_email ? trim($cliente->cli_email) : null,
                        'contacto' => $cliente->cli_contacto ? trim($cliente->cli_contacto) : null,
                        'sector' => $cliente->cli_codigocrub ? trim($cliente->cli_codigocrub) : null,
                    ];
                });

            return response()->json($clientes);

        } catch (\Exception $e) {
            Log::error('Error buscando clientes:', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }

    // API para obtener datos de un cliente específico
    public function obtenerCliente($codigo)
    {
        try {
            Log::info('=== API OBTENER CLIENTE ===');
            Log::info('Código recibido:', ['codigo_original' => $codigo]);
            
            $codigoPadded = str_pad($codigo, 10, ' ', STR_PAD_RIGHT);
            Log::info('Código con padding:', ['codigo_padded' => "'" . $codigoPadded . "'"]);
            
            $cliente = Clientes::where('cli_codigo', $codigoPadded)
                ->where('cli_estado', true)
                ->first();

            if (!$cliente) {
                Log::warning('Cliente no encontrado:', ['codigo' => $codigo]);
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            $clienteData = [
                'codigo' => trim($cliente->cli_codigo),
                'razon_social' => trim($cliente->cli_razonsocial),
                'fantasia' => $cliente->cli_fantasia ? trim($cliente->cli_fantasia) : '',
                'direccion' => $cliente->cli_direccion ? trim($cliente->cli_direccion) : '',
                'localidad' => $cliente->cli_localidad ? trim($cliente->cli_localidad) : '',
                'cuit' => $cliente->cli_cuit ?: '',
                'codigo_postal' => $cliente->cli_codigopostal ? trim($cliente->cli_codigopostal) : '',
                'telefono' => $cliente->cli_telefono ?: '',
                'email' => $cliente->cli_email ?: '',
                'contacto' => $cliente->cli_contacto ? trim($cliente->cli_contacto) : '',
                'sector' => $cliente->cli_codigocrub ? trim($cliente->cli_codigocrub) : '',
                'condicion_pago' => $cliente->cli_codigopag ? trim($cliente->cli_codigopag) : '',
                'lista_precios' => $cliente->cli_codigolp ? trim($cliente->cli_codigolp) : '',
                'nro_precio' => $cliente->cli_nroprecio ?: 1,
                'descuento_global' => (float) ($cliente->cli_descuentoglobal ?? 0),
                'descuentos_sector' => $this->obtenerDescuentosSectorCliente($cliente),
            ];
            
            Log::info('Datos del cliente encontrado:', $clienteData);
            return response()->json($clienteData);

        } catch (\Exception $e) {
            Log::error('Error obteniendo cliente:', ['codigo' => $codigo, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * API para obtener ensayos (muestras) disponibles
     */
    public function obtenerEnsayos(Request $request)
    {
        $termino = $request->get('q', '');
        
        $ensayos = CotioItems::muestras()
            ->when($termino, function($query, $termino) {
                return $query->buscar($termino);
            })
            ->with(['metodoAnalitico', 'componentesAsociados:id,cotio_descripcion'])
            ->orderBy('cotio_descripcion')
            ->get();

        return response()->json($ensayos->map(function($ensayo) {
            // Intentar obtener el método desde la relación primero
            $metodoCodigo = optional($ensayo->metodoAnalitico)->metodo_codigo 
                ? trim(optional($ensayo->metodoAnalitico)->metodo_codigo) 
                : ($ensayo->metodo ? trim($ensayo->metodo) : null);
            
            $metodoDescripcion = optional($ensayo->metodoAnalitico)->metodo_descripcion;
            
            // Si tenemos el código pero no la descripción, intentar cargar el método
            if ($metodoCodigo && !$metodoDescripcion) {
                $metodo = \App\Models\Metodo::where('metodo_codigo', trim($metodoCodigo))->first();
                $metodoDescripcion = $metodo ? $metodo->metodo_descripcion : null;
            }
            
            return [
                'id' => $ensayo->id,
                'codigo' => str_pad($ensayo->id, 15, '0', STR_PAD_LEFT), // Generar código
                'descripcion' => $ensayo->cotio_descripcion,
                'es_muestra' => $ensayo->es_muestra,
                'metodo_codigo' => $metodoCodigo,
                'metodo_descripcion' => $metodoDescripcion,
                'text' => $ensayo->cotio_descripcion, // Para select2
                'componentes_default' => $ensayo->componentesAsociados->pluck('id')->values(),
            ];
        }));
    }

    /**
     * API para obtener componentes (análisis) disponibles
     */
    public function obtenerComponentes(Request $request)
    {
        $termino = $request->get('q', '');
        
        $componentes = CotioItems::componentes()
            ->when($termino, function($query, $termino) {
                return $query->buscar($termino);
            })
            ->with(['metodoAnalitico', 'matriz'])
            ->orderBy('cotio_descripcion')
            ->get();

        return response()->json($componentes->map(function($componente) {
            // Intentar obtener el método desde la relación primero
            $metodoCodigo = optional($componente->metodoAnalitico)->metodo_codigo 
                ? trim(optional($componente->metodoAnalitico)->metodo_codigo) 
                : ($componente->metodo ? trim($componente->metodo) : null);
            
            $metodoDescripcion = optional($componente->metodoAnalitico)->metodo_descripcion;
            
            // Si tenemos el código pero no la descripción, intentar cargar el método
            if ($metodoCodigo && !$metodoDescripcion) {
                $metodo = \App\Models\Metodo::where('metodo_codigo', trim($metodoCodigo))->first();
                $metodoDescripcion = $metodo ? $metodo->metodo_descripcion : null;
            }
            
            $matrizCodigo = $componente->matriz_codigo ? trim($componente->matriz_codigo) : null;
            $matrizDescripcion = optional($componente->matriz)->matriz_descripcion
                ? trim(optional($componente->matriz)->matriz_descripcion)
                : null;
            $precio = $componente->precio ?? 5000.00;

            return [
                'id' => $componente->id,
                'codigo' => str_pad($componente->id, 15, '0', STR_PAD_LEFT), // Generar código
                'descripcion' => $componente->cotio_descripcion,
                'es_muestra' => $componente->es_muestra,
                'metodo_codigo' => $metodoCodigo,
                'metodo_descripcion' => $metodoDescripcion,
                'unidad_medida' => $componente->unidad_medida,
                'limites_establecidos' => $componente->limites_establecidos,
                'precio' => $precio, // Precio por defecto 5000 si no existe
                'matriz_codigo' => $matrizCodigo,
                'matriz_descripcion' => $matrizDescripcion,
                'ley_normativa_id' => $componente->ley_normativa_id ?? null,
                'text' => $componente->cotio_descripcion // Para select2
            ];
        }));
    }

    /**
     * API para obtener métodos de muestreo
     */
    public function obtenerMetodosMuestreo(Request $request)
    {
        $termino = $request->get('q', '');
        
        $metodos = Metodo::when($termino, function($query, $termino) {
                $query->where('metodo_codigo', 'ILIKE', "%{$termino}%")
                      ->orWhere('metodo_descripcion', 'ILIKE', "%{$termino}%");
            })
            ->orderBy('metodo_codigo')
            ->get();

        return response()->json($metodos->map(function($m) {
            $codigo = trim($m->metodo_codigo);
            return [
                'id' => $codigo,
                'codigo' => $codigo,
                'descripcion' => $m->metodo_descripcion,
                'text' => $codigo . ' - ' . $m->metodo_descripcion,
            ];
        }));
    }

    /**
     * API para obtener métodos de análisis
     */
    public function obtenerMetodosAnalisis(Request $request)
    {
        $termino = $request->get('q', '');
        
        $metodos = Metodo::when($termino, function($query, $termino) {
                $query->where('metodo_codigo', 'ILIKE', "%{$termino}%")
                      ->orWhere('metodo_descripcion', 'ILIKE', "%{$termino}%");
            })
            ->orderBy('metodo_codigo')
            ->get();

        return response()->json($metodos->map(function($m) {
            $codigo = trim($m->metodo_codigo);
            return [
                'id' => $codigo,
                'codigo' => $codigo,
                'descripcion' => $m->metodo_descripcion,
                'text' => $codigo . ' - ' . $m->metodo_descripcion,
            ];
        }));
    }

    /**
     * API para obtener leyes normativas
     */
    public function obtenerLeyesNormativas(Request $request)
    {
        $termino = $request->get('q', '');
        
        $leyes = LeyNormativa::activas()
            ->when($termino, function($query, $termino) {
                return $query->buscar($termino);
            })
            ->orderBy('grupo')
            ->orderBy('codigo')
            ->get();

        return response()->json($leyes->map(function($ley) {
            return [
                'id' => $ley->id,
                'codigo' => $ley->codigo,
                'nombre' => $ley->nombre,
                'grupo' => $ley->grupo,
                'articulo' => $ley->articulo,
                'descripcion' => $ley->descripcion,
                'organismo_emisor' => $ley->organismo_emisor,
                'fecha_vigencia' => $ley->fecha_vigencia,
                'nombre_completo' => $ley->nombre_completo,
                'text' => $ley->codigo . ' - ' . $ley->nombre_completo // Para select2
            ];
        }));
    }

    /**
     * Procesar ensayos y componentes para crear registros en cotio
     */
    private function procesarEnsayosYComponentes(Request $request, $cotiNum, bool $reemplazarExistentes = false)
    {
        Log::info('=== PROCESANDO ENSAYOS Y COMPONENTES ===');
        
        try {
            $ensayosData = $request->ensayos_data ? json_decode($request->ensayos_data, true) : [];
            $componentesData = $request->componentes_data ? json_decode($request->componentes_data, true) : [];
            
            Log::info('Datos recibidos:', [
                'ensayos_count' => count($ensayosData),
                'componentes_count' => count($componentesData),
                'ensayos_raw' => $request->ensayos_data,
                'componentes_raw' => $request->componentes_data
            ]);

            if ($reemplazarExistentes) {
                Cotio::where('cotio_numcoti', $cotiNum)->delete();
                Log::info('Registros anteriores de cotio eliminados para la cotización.', ['coti_num' => $cotiNum]);
            }

            // Procesar ensayos (muestras con cotio_subitem = 0)
            foreach ($ensayosData as $ensayo) {
                Log::info('Procesando ensayo:', $ensayo);
                
                // Buscar el código de producto correcto en la tabla prod
                $prodCodigo = $this->buscarCodigoProducto($ensayo['descripcion'], true);
                
                if (!$prodCodigo) {
                    Log::warning('No se encontró código de producto para ensayo:', $ensayo);
                    continue;
                }
                
                $cotioEnsayo = new Cotio();
                $cotioEnsayo->cotio_numcoti = $cotiNum;
                $cotioEnsayo->cotio_item = $ensayo['item'];
                $cotioEnsayo->cotio_subitem = 0; // Las muestras siempre tienen subitem 0
                $cotioEnsayo->cotio_codigoprod = $prodCodigo;
                $cotioEnsayo->cotio_cantidad = $this->parseDecimalValue($ensayo['cantidad'] ?? 1) ?? 1;
                $precioEnsayo = isset($ensayo['precio']) && $ensayo['precio'] !== ''
                    ? $this->parseDecimalValue($ensayo['precio'])
                    : null;
                $cotioEnsayo->cotio_precio = ($precioEnsayo && $precioEnsayo > 0) ? $precioEnsayo : null;
                $cotioEnsayo->cotio_descripcion = $ensayo['descripcion'];
                $cotioEnsayo->cotio_codigoum = null;
                $cotioEnsayo->cotio_codigometodo = null;
                $cotioEnsayo->enable_muestreo = false;
                
                $cotioEnsayo->save();
                Log::info('Ensayo guardado:', ['cotio_id' => $cotioEnsayo->id, 'prod_codigo' => $prodCodigo]);
            }

            // Procesar componentes (análisis con cotio_subitem > 0)
            foreach ($componentesData as $componente) {
                Log::info('Procesando componente:', $componente);
                
                // Encontrar el ensayo asociado para obtener el cotio_item correcto
                $ensayoAsociado = collect($ensayosData)->firstWhere('item', $componente['ensayo_asociado']);
                
                if (!$ensayoAsociado) {
                    Log::warning('Ensayo asociado no encontrado para componente:', $componente);
                    continue;
                }
                
                // Buscar el código de producto correcto en la tabla prod
                $prodCodigo = $this->buscarCodigoProducto($componente['descripcion'], false);
                
                if (!$prodCodigo) {
                    Log::warning('No se encontró código de producto para componente:', $componente);
                    continue;
                }
                
                // Contar cuántos componentes ya existen para este ensayo para asignar el subitem
                $componentesExistentes = Cotio::where('cotio_numcoti', $cotiNum)
                    ->where('cotio_item', $ensayoAsociado['item'])
                    ->where('cotio_subitem', '>', 0)
                    ->count();
                
                $cotioComponente = new Cotio();
                $cotioComponente->cotio_numcoti = $cotiNum;
                $cotioComponente->cotio_item = $ensayoAsociado['item'];
                $cotioComponente->cotio_subitem = $componentesExistentes + 1; // Incrementar subitem
                $cotioComponente->cotio_codigoprod = $prodCodigo;
                $cotioComponente->cotio_cantidad = $this->parseDecimalValue($componente['cantidad'] ?? 1) ?? 1;
                $cotioComponente->cotio_precio = $this->parseDecimalValue($componente['precio'] ?? null);
                $cotioComponente->cotio_descripcion = $componente['descripcion'];
                $unidadMedida = $componente['unidad_medida'] ?? null;
                $metodoCodigo = $componente['metodo_codigo'] ?? null;
                $metodoAnalisis = $componente['metodo_analisis_id'] ?? null;
                $metodoMuestreo = $componente['metodo_muestreo_id'] ?? null;
                $limiteDeteccion = $this->parseDecimalValue($componente['limite_deteccion'] ?? null);

                // Procesar unidad de medida
                if ($unidadMedida) {
                    $unidadTrim = trim($unidadMedida);
                    if ($unidadTrim !== '') {
                        $unidadCodigo = $this->truncateAndPad($unidadTrim, 10);
                        $unidadExiste = DB::table('um')->where('um_codigo', $unidadCodigo)->exists();

                        if (!$unidadExiste) {
                            try {
                                $columns = DB::getSchemaBuilder()->getColumnListing('um');
                                $payload = [];

                                if (in_array('um_codigo', $columns)) {
                                    $payload['um_codigo'] = $unidadCodigo;
                                }

                                if (in_array('um_descripcion', $columns)) {
                                    $payload['um_descripcion'] = Str::upper($unidadTrim);
                                }

                                if (in_array('um_factor', $columns)) {
                                    $payload['um_factor'] = 1;
                                }

                                if (in_array('um_estado', $columns)) {
                                    $payload['um_estado'] = true;
                                }

                                if (in_array('created_at', $columns)) {
                                    $payload['created_at'] = now();
                                }

                                if (in_array('updated_at', $columns)) {
                                    $payload['updated_at'] = now();
                                }

                                if (!empty($payload)) {
                                    DB::table('um')->insert($payload);
                                    Log::info('Unidad de medida creada automáticamente', [
                                        'unidad' => $unidadTrim,
                                        'unidad_codigo' => $unidadCodigo
                                    ]);
                                } else {
                                    Log::warning('No se pudo crear unidad de medida: sin columnas conocidas', [
                                        'unidad' => $unidadTrim
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error creando unidad de medida automáticamente', [
                                    'unidad' => $unidadTrim,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        if (DB::table('um')->where('um_codigo', $unidadCodigo)->exists()) {
                            $cotioComponente->cotio_codigoum = $unidadCodigo;
                            Log::info('Unidad de medida asignada al componente', [
                                'unidad_codigo' => $unidadCodigo,
                                'componente' => $componente['descripcion']
                            ]);
                        } else {
                            $cotioComponente->cotio_codigoum = null;
                        }
                    }
                }

                // Procesar método: buscar SOLO en la tabla metodo (modelo Metodo)
                if ($metodoCodigo) {
                    $metodoCodigoTrim = trim($metodoCodigo);
                    if ($metodoCodigoTrim !== '') {
                        // Buscar en la tabla metodo
                        if (Metodo::where('metodo_codigo', $metodoCodigoTrim)->exists()) {
                            $cotioComponente->cotio_codigometodo = $this->truncateAndPad($metodoCodigoTrim, 15);
                            Log::info('Método asignado a cotio_codigometodo desde tabla metodo', [
                                'metodo_codigo' => $metodoCodigoTrim,
                                'componente' => $componente['descripcion']
                            ]);
                        } else {
                            Log::warning('Método no encontrado en tabla metodo', [
                                'metodo_codigo' => $metodoCodigoTrim,
                                'componente' => $componente['descripcion']
                            ]);
                            $cotioComponente->cotio_codigometodo = null;
                        }
                    } else {
                        $cotioComponente->cotio_codigometodo = null;
                    }
                } else {
                    $cotioComponente->cotio_codigometodo = null;
                }

                // Procesar método de análisis: cotio_codigometodo_analisis referencia metodos_analisis
                $codigoMetodoAnalisis = null;
                if ($metodoAnalisis) {
                    $codigoMetodoAnalisis = trim($metodoAnalisis);
                } elseif ($metodoCodigo) {
                    // Si el metodoCodigo viene de la tabla metodo, intentar usarlo para análisis
                    $codigoMetodoAnalisis = trim($metodoCodigo);
                }

                if ($codigoMetodoAnalisis) {
                    $codigoMetodoAnalisisTrim = trim($codigoMetodoAnalisis);
                    // Verificar que existe en metodos_analisis antes de asignar
                    if (MetodoAnalisis::where('codigo', $codigoMetodoAnalisisTrim)->exists()) {
                        $cotioComponente->cotio_codigometodo_analisis = $this->truncateAndPad($codigoMetodoAnalisisTrim, 15);
                        Log::info('Método de análisis asignado al componente', [
                            'metodo_analisis' => $codigoMetodoAnalisisTrim,
                            'componente' => $componente['descripcion']
                        ]);
                    } else {
                        Log::warning('Método de análisis no encontrado en metodos_analisis', [
                            'codigo' => $codigoMetodoAnalisisTrim,
                            'componente' => $componente['descripcion']
                        ]);
                        $cotioComponente->cotio_codigometodo_analisis = null;
                    }
                } else {
                    $cotioComponente->cotio_codigometodo_analisis = null;
                }

                if (!is_null($limiteDeteccion)) {
                    $cotioComponente->limite_deteccion = $limiteDeteccion;
                }

                $cotioComponente->enable_muestreo = false;
                
                $cotioComponente->save();
                Log::info('Componente guardado:', ['cotio_id' => $cotioComponente->id, 'prod_codigo' => $prodCodigo]);
            }
            
            Log::info('Ensayos y componentes procesados exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error procesando ensayos y componentes:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar la excepción para no interrumpir la creación de la cotización
        }
    }

    /**
     * Buscar código de producto en la tabla prod basándose en la descripción
     */
    private function buscarCodigoProducto($descripcion, $esMuestra = true)
    {
        try {
            Log::info('Buscando código de producto:', [
                'descripcion' => $descripcion,
                'es_muestra' => $esMuestra
            ]);
            
            // Buscar en la tabla prod por descripción exacta
            $producto = DB::table('prod')
                ->where('prod_descripcion', $descripcion)
                ->where('prod_estado', true)
                ->first();
            
            if ($producto) {
                Log::info('Producto encontrado por descripción exacta:', [
                    'prod_codigo' => $producto->prod_codigo,
                    'descripcion' => $producto->prod_descripcion
                ]);
                return $producto->prod_codigo;
            }
            
            // Si no se encuentra por descripción exacta, buscar por descripción similar
            $producto = DB::table('prod')
                ->where('prod_descripcion', 'ILIKE', "%{$descripcion}%")
                ->where('prod_estado', true)
                ->first();
            
            if ($producto) {
                Log::info('Producto encontrado por descripción similar:', [
                    'prod_codigo' => $producto->prod_codigo,
                    'descripcion' => $producto->prod_descripcion
                ]);
                return $producto->prod_codigo;
            }
            
            // Si no se encuentra, crear un código genérico basado en el tipo
            $codigoGenerico = $esMuestra ? '000010000000000' : '000010000100006'; // Códigos de ejemplo de la investigación
            
            Log::warning('No se encontró producto, usando código genérico:', [
                'descripcion' => $descripcion,
                'codigo_generico' => $codigoGenerico
            ]);
            
            return $codigoGenerico;
            
        } catch (\Exception $e) {
            Log::error('Error buscando código de producto:', [
                'error' => $e->getMessage(),
                'descripcion' => $descripcion
            ]);
            
            // Retornar código genérico en caso de error
            return $esMuestra ? '000010000000000' : '000010000100006';
        }
    }
    private function normalizarCodigoSector(?string $sector): ?string
    {
        if (is_null($sector)) {
            return null;
        }

        $valor = strtoupper(trim($sector));
        if ($valor === '') {
            return null;
        }

        $map = [
            'LABORATORIO' => 'LAB',
            'HIGIENE Y SEGURIDAD' => 'HYS',
            'MICROBIOLOGIA' => 'MIC',
            'CROMATOGRAFIA' => 'CRO',
            'LAB' => 'LAB',
            'HYS' => 'HYS',
            'MIC' => 'MIC',
            'CRO' => 'CRO',
        ];

        if (isset($map[$valor])) {
            return $map[$valor];
        }

        $primerosTres = substr($valor, 0, 3);
        $mapBasico = [
            'LAB' => 'LAB',
            'HYS' => 'HYS',
            'MIC' => 'MIC',
            'CRO' => 'CRO',
        ];

        return $mapBasico[$primerosTres] ?? null;
    }

    private function obtenerDescuentosSectorCliente(?Clientes $cliente): array
    {
        if (!$cliente) {
            return [
                'LAB' => 0.0,
                'HYS' => 0.0,
                'MIC' => 0.0,
                'CRO' => 0.0,
            ];
        }

        return [
            'LAB' => (float) ($cliente->cli_sector_laboratorio_pct ?? 0.0),
            'HYS' => (float) ($cliente->cli_sector_higiene_pct ?? 0.0),
            'MIC' => (float) ($cliente->cli_sector_microbiologia_pct ?? 0.0),
            'CRO' => (float) ($cliente->cli_sector_cromatografia_pct ?? 0.0),
        ];
    }

    private function obtenerDescuentoSector(?Clientes $cliente, ?string $sector): float
    {
        if (!$cliente) {
            return 0.0;
        }

        $codigoSector = $this->normalizarCodigoSector($sector);
        if (!$codigoSector) {
            return 0.0;
        }

        $descuentos = $this->obtenerDescuentosSectorCliente($cliente);

        return (float) ($descuentos[$codigoSector] ?? 0.0);
    }

    private function calcularDescuentoCliente(?Clientes $cliente, ?string $sector): float
    {
        if (!$cliente) {
            return 0.0;
        }

        $global = (float) ($cliente->cli_descuentoglobal ?? 0.0);
        $sectorExtra = $this->obtenerDescuentoSector($cliente, $sector);

        return $global + $sectorExtra;
    }

    private function calcularDescuentoCotizacion(?Ventas $cotizacion): float
    {
        if (!$cotizacion) {
            return 0.0;
        }

        $cliente = $cotizacion->cliente;
        $sectorCodigoOriginal = $cotizacion->coti_sector ?? optional($cliente)->cli_codigocrub;
        $sectorCodigo = $this->normalizarCodigoSector($sectorCodigoOriginal);

        // Prioridad: primero descuentos de la cotización, luego del cliente
        // Descuento global: usar el de la cotización si existe, sino el del cliente
        $descuentoGlobal = 0.0;
        if (isset($cotizacion->coti_descuentoglobal) && $cotizacion->coti_descuentoglobal > 0) {
            $descuentoGlobal = (float) $cotizacion->coti_descuentoglobal;
        } elseif ($cliente) {
            $descuentoGlobal = (float) ($cliente->cli_descuentoglobal ?? 0.0);
        }

        // Descuento sector: usar el de la cotización si existe, sino el del cliente
        $descuentoSector = 0.0;
        if ($sectorCodigo) {
            $descuentoSector = $this->obtenerDescuentoSectorCotizacion($cotizacion, $sectorCodigo);
        }
        
        // Si no hay descuento de sector en la cotización, usar el del cliente
        if ($descuentoSector == 0.0 && $cliente) {
            $descuentoSector = $this->obtenerDescuentoSector($cliente, $sectorCodigo);
        }

        return $descuentoGlobal + $descuentoSector;
    }

    private function obtenerDescuentosSectorCotizacion(?Ventas $cotizacion): array
    {
        if (!$cotizacion) {
            return [
                'LAB' => 0.0,
                'HYS' => 0.0,
                'MIC' => 0.0,
                'CRO' => 0.0,
            ];
        }

        return [
            'LAB' => (float) ($cotizacion->coti_sector_laboratorio_pct ?? 0.0),
            'HYS' => (float) ($cotizacion->coti_sector_higiene_pct ?? 0.0),
            'MIC' => (float) ($cotizacion->coti_sector_microbiologia_pct ?? 0.0),
            'CRO' => (float) ($cotizacion->coti_sector_cromatografia_pct ?? 0.0),
        ];
    }

    private function obtenerDescuentoSectorCotizacion(?Ventas $cotizacion, ?string $sectorCodigo): float
    {
        if (!$cotizacion || !$sectorCodigo) {
            return 0.0;
        }

        $descuentos = $this->obtenerDescuentosSectorCotizacion($cotizacion);
        return (float) ($descuentos[$sectorCodigo] ?? 0.0);
    }

}