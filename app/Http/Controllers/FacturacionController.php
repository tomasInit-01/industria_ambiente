<?php

namespace App\Http\Controllers;

use App\Models\Coti;
use App\Models\CotioInstancia;
use App\Models\CotioInstanciaAnalisis;
use App\Models\CotioInstanciaMuestra;
use App\Models\CotioInstanciaMuestraAnalisis;
use App\Models\CotioInstanciaMuestraMuestra;
use App\Models\CotioInstanciaMuestraMuestraAnalisis;
use App\Models\Matriz;
use App\Models\Factura;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Afip;
use Barryvdh\DomPDF\Facade\Pdf;


class FacturacionController extends Controller
{
public function index(Request $request)
{
    $query = Factura::with('cotizacion')
                ->orderBy('created_at', 'desc');

    // Filtrar por estado si se especifica
    if ($request->has('estado') && $request->estado != '') {
        $query->where('estado', $request->estado);
    }

    // Filtrar por cotización si se especifica
    if ($request->has('cotizacion') && $request->cotizacion != '') {
        $query->where('cotizacion_id', $request->cotizacion);
    }

    // Filtrar por fecha si se especifica
    if ($request->has('fecha_desde') && $request->fecha_desde != '') {
        $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
    }

    if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
        $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
    }

    $facturas = $query->paginate(20);

    // Obtener estadísticas
    $estadisticas = [
        'total_facturas' => Factura::count(),
        'monto_total' => Factura::sum('monto_total'),
        'facturas_pendientes' => CotioInstancia::where('facturado', false)->where('enable_inform', true)->count(),
        'facturas_facturadas' => CotioInstancia::where('facturado', true)->where('enable_inform', true)->count(),
    ];


    $baseQuery = CotioInstancia::with([
        'cotizacion.matriz',
        'tareas' => function($query) {
            $query->where('enable_inform', true)
                    ->orderBy('cotio_subitem');
        },
        'cotizacion.instancias'
    ])
    ->where('enable_inform', true)
    ->where('facturado', false)
    ->where('cotio_subitem', 0);

    // Vista de lista o documento
    $pagination = $baseQuery
        ->orderBy('cotio_numcoti', $request->get('orden_cotizacion', 'desc'))
        ->orderBy('cotio_item', 'asc')
        ->orderBy('instance_number', 'asc')
        ->paginate(20);

    // Agrupar por cotización
    $informesPorCotizacion = $pagination->groupBy('cotio_numcoti')->map(function ($group) {
        $cotizacion = $group->first()->cotizacion;
        
        return [
            'cotizacion' => $cotizacion,
            'muestras' => $group->map(function($muestra) {
                return $muestra;
            })
        ];
    });



    return view('facturacion.index', compact('facturas', 'estadisticas', 'request', 'informesPorCotizacion'));
}


public function facturar($coti_num)
{
    $cotizacion = Coti::findOrFail($coti_num);

    // Cargar tareas con relaciones necesarias
    $tareas = $cotizacion->tareas()
                ->orderBy('cotio_item')
                ->orderBy('cotio_subitem')
                ->get();

    // Obtener variables requeridas por tipo de muestra
    $tiposMuestra = $tareas->pluck('cotio_descripcion')->unique()->toArray();

    // Cargar todas las instancias (muestras y análisis)
    $todasInstancias = CotioInstancia::where('cotio_numcoti', $coti_num)
                        ->with(['responsablesMuestreo', 'responsablesAnalisis'])
                        ->get()
                        ->groupBy(['cotio_item', 'cotio_subitem', 'instance_number']);
    
    // Filtrar solo las muestras (cotio_subitem = 0) que tienen enable_inform = true
    $muestrasParaFacturar = CotioInstancia::where('cotio_numcoti', $coti_num)
                        ->where('enable_inform', true)
                        ->where('cotio_subitem', 0)
                        ->with(['responsablesMuestreo', 'responsablesAnalisis'])
                        ->get()
                        ->groupBy(['cotio_item', 'cotio_subitem', 'instance_number']);

    // Obtener usuarios muestreadores
    $usuarios = [];

    $agrupadas = [];

    // Solo trabajar con muestras que tienen enable_inform = true
    foreach ($muestrasParaFacturar as $item => $subitems) {
        foreach ($subitems as $subitem => $instances) {
            if ($subitem == 0) { // Solo muestras principales
                foreach ($instances as $instanceNumber => $instanciaCollection) {
                    $instancia = $instanciaCollection->first();
                    $tarea = $tareas->where('cotio_item', $item)->where('cotio_subitem', 0)->first();
                    
                    if ($instancia && $tarea) {
                        $analisisMuestra = $this->getAnalisisForMuestra($tareas, $item, $instanceNumber, $todasInstancias);

                        $agrupadas[] = [
                            'categoria' => (object) array_merge($tarea->toArray(), [
                                'instance_number' => $instancia->instance_number,
                                'original_item' => $tarea->cotio_item,
                                'display_item' => '#' . $tarea->cotio_item,
                            ]),
                            'instancia' => $instancia,
                            'tareas' => $analisisMuestra,
                            'responsables' => $instancia->responsablesMuestreo ?? collect(),
                        ];
                    }
                }
            }
        }
    }

    return view('facturacion.show', compact(
        'cotizacion', 
        'tareas', 
        'usuarios', 
        'agrupadas', 
    ));
}





protected function getOrCreateInstancia($numcoti, $item, $subitem, $instance, $instanciasExistentes)
{
    if (
        isset($instanciasExistentes[$item]) &&
        isset($instanciasExistentes[$item][$subitem]) &&
        isset($instanciasExistentes[$item][$subitem][$instance])
    ) {
        return $instanciasExistentes[$item][$subitem][$instance]->first();
    }

    return new CotioInstancia([
        'cotio_numcoti' => $numcoti,
        'cotio_item' => $item,
        'cotio_subitem' => $subitem,
        'instance_number' => $instance,
        'responsable_muestreo' => null,
        'fecha_muestreo' => null,
        'enable_inform' => true,
    ]);
}

protected function getAnalisisForMuestra($tareas, $item, $instance, $todasInstancias)
{
    $analisis = [];

    foreach ($tareas as $tarea) {
        if ($tarea->cotio_item == $item && $tarea->cotio_subitem != 0) {
            // Buscar la instancia del análisis en todas las instancias
            if (isset($todasInstancias[$item]) && 
                isset($todasInstancias[$item][$tarea->cotio_subitem]) && 
                isset($todasInstancias[$item][$tarea->cotio_subitem][$instance])) {
                
                $instanciaAnalisis = $todasInstancias[$item][$tarea->cotio_subitem][$instance]->first();

                $tareaClonada = clone $tarea;
                $tareaClonada->instancia = $instanciaAnalisis;
                $tareaClonada->original_item = $tarea->cotio_item;
                
                // Agregar los datos de resultado directamente a la tarea clonada
                $tareaClonada->resultado = $instanciaAnalisis->resultado;
                $tareaClonada->resultado_2 = $instanciaAnalisis->resultado_2;
                $tareaClonada->resultado_3 = $instanciaAnalisis->resultado_3;
                $tareaClonada->resultado_final = $instanciaAnalisis->resultado_final;
                $tareaClonada->observacion_resultado = $instanciaAnalisis->observacion_resultado;
                $tareaClonada->observacion_resultado_2 = $instanciaAnalisis->observacion_resultado_2;
                $tareaClonada->observacion_resultado_3 = $instanciaAnalisis->observacion_resultado_3;
                $tareaClonada->observacion_resultado_final = $instanciaAnalisis->observacion_resultado_final;
                $tareaClonada->observaciones_ot = $instanciaAnalisis->observaciones_ot;

                $analisis[] = $tareaClonada;
            }
        }
    }

    return $analisis;
}
    




public function generarFacturaArca(Request $request, $coti_num)
{
    Log::info('Datos recibidos:', $request->all());
    try {
        $request->validate([
            'cotizacion_id' => 'required|numeric',
            'muestras' => 'array|nullable',
            'analisis' => 'array|nullable',
            'muestras.*' => 'numeric|exists:cotio_instancias,id',
            'analisis.*' => 'numeric|exists:cotio_instancias,id',
        ], [
            'analisis.*.numeric' => 'El ID del análisis :index debe ser un número.',
            'analisis.*.exists' => 'El ID del análisis :index no existe en la base de datos.',
            'muestras.*.numeric' => 'El ID de la muestra :index debe ser un número.',
            'muestras.*.exists' => 'El ID de la muestra :index no existe en la base de datos.',
        ]);

        $cotizacion = Coti::findOrFail($coti_num);
        $muestrasSeleccionadas = $request->input('muestras', []);
        $analisisSeleccionados = $request->input('analisis', []);

        // Cargar instancias de muestras
        $muestras = CotioInstancia::whereIn('id', $muestrasSeleccionadas)
                    ->with(['cotizacion', 'responsablesAnalisis'])
                    ->get();

        // Cargar instancias de análisis
        $analisis = CotioInstancia::whereIn('id', $analisisSeleccionados)
                    ->with(['cotizacion'])
                    ->get();

        // Verificar que los datos existan
        if ($muestras->isEmpty() && $analisis->isEmpty()) {
            return redirect()->back()->with('error', 'No se seleccionaron muestras ni análisis válidos.');
        }

        // Generar items para la factura
        $items = [];
        $montoTotal = 0;

        // Agregar muestras como items
        foreach ($muestras as $muestra) {
            $precio = $this->calcularPrecioMuestra($muestra);
            $items[] = [
                'tipo' => 'muestra',
                'descripcion' => "Muestra - {$muestra->cotio_descripcion}",
                'identificacion' => $muestra->cotio_identificacion ?? 'N/A',
                'cantidad' => 1,
                'precio_unitario' => $precio,
                'subtotal' => $precio,
                'instancia_id' => $muestra->id
            ];
            $montoTotal += $precio;
        }

        // Agregar análisis como items
        foreach ($analisis as $analisis_item) {
            $precio = $this->calcularPrecioAnalisis($analisis_item);
            $items[] = [
                'tipo' => 'analisis',
                'descripcion' => "Análisis - {$analisis_item->cotio_descripcion}",
                'resultado' => $analisis_item->resultado_final ?? 'N/A',
                'cantidad' => 1,
                'precio_unitario' => $precio,
                'subtotal' => $precio,
                'instancia_id' => $analisis_item->id
            ];
            $montoTotal += $precio;
        }

        // Preparar datos del cliente
        $clienteData = [
            'razon_social' => $cotizacion->coti_empresa ?? env('EMPRESA_RAZON_SOCIAL', 'Cliente Prueba'),
            'cuit' => $cotizacion->coti_cuit ?? env('AFIP_CUIT', '20111111112'),
            'domicilio' => $cotizacion->coti_direccioncli ?? env('EMPRESA_DOMICILIO', 'Domicilio Prueba'),
            'localidad' => $cotizacion->coti_localidad ?? 'CABA',
            'provincia' => $cotizacion->coti_partido ?? 'Buenos Aires',
            'email' => $cotizacion->coti_mail ?? 'pruebas@afip.com',
        ];

        // Generar factura con ARCA/AFIP
        Log::info('Generando factura con precios reales en entorno de prueba', [
            'cotizacion_id' => $coti_num,
            'monto_total' => $montoTotal,
            'cantidad_items' => count($items)
        ]);

        $resultadoFactura = $this->integrarConArca($clienteData, $items, $montoTotal, $cotizacion);

        if ($resultadoFactura['success']) {
            try {
                // Obtener la primera muestra para la descripción e instancia
                $muestraPrincipal = $muestras->first();
                
                $factura = $this->guardarFacturacion([
                    'cotizacion_id' => $coti_num,
                    'cotio_descripcion' => $muestraPrincipal ? $muestraPrincipal->cotio_descripcion : 'Muestra no especificada',
                    'instance_number' => $muestraPrincipal ? $muestraPrincipal->instance_number : 0,
                    'numero_factura' => $resultadoFactura['numero_factura'],
                    'cae' => $resultadoFactura['cae'],
                    'fecha_vencimiento_cae' => $resultadoFactura['fecha_vencimiento'],
                    'monto_total' => $montoTotal,
                    'items' => $items,
                    'estado' => 'aprobada',
                    'muestras_ids' => $muestrasSeleccionadas,
                    'analisis_ids' => $analisisSeleccionados
                ]);
        
                return redirect()->back()->with('success', 'Factura generada exitosamente: ' . $resultadoFactura['numero_factura']);
            } catch (\Exception $e) {
                Log::error('Error al guardar factura después de generarla en AFIP: ' . $e->getMessage());
                return redirect()->back()->with('success', 'Factura generada en AFIP: ' . $resultadoFactura['numero_factura'] . ' (Error al guardar en BD: ' . $e->getMessage() . ')');
            }
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Errores de validación: ' . json_encode($e->errors()));
        return redirect()->back()->with('error', 'Errores en los datos enviados: ' . implode(', ', $e->errors()['analisis.0'] ?? $e->errors()));
    } catch (\Exception $e) {
        Log::error('Error al generar factura ARCA: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error interno al generar la factura: ' . $e->getMessage());
    }
}

private function guardarFacturacion($data)
{
    try {
        Log::info('Intentando guardar factura con datos:', $data);

        // Obtener la cotización para extraer datos del cliente
        $cotizacion = Coti::find($data['cotizacion_id']);
        // dd($cotizacion);
        
        // Procesar fecha de vencimiento CAE
        $fechaVencimiento = null;
        if (isset($data['fecha_vencimiento_cae'])) {
            $fechaStr = $data['fecha_vencimiento_cae'];
            Log::info('Procesando fecha CAE:', ['fecha_raw' => $fechaStr, 'length' => strlen($fechaStr)]);
            
            try {
                if (strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                    $fechaVencimiento = Carbon::createFromFormat('Ymd', $fechaStr)->format('Y-m-d');
                } else {
                    $fechaVencimiento = Carbon::parse($fechaStr)->format('Y-m-d');
                }
                Log::info('Fecha CAE procesada exitosamente:', ['fecha_procesada' => $fechaVencimiento]);
            } catch (\Exception $dateException) {
                Log::error('Error al procesar fecha CAE:', [
                    'fecha_original' => $fechaStr,
                    'error' => $dateException->getMessage()
                ]);
                $fechaVencimiento = Carbon::now()->addDays(10)->format('Y-m-d');
            }
        }

        // Procesar items
        $items = $data['items'];
        if (is_array($items)) {
            $items = json_encode($items, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($items)) {
            json_decode($items);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Items no es JSON válido, convirtiendo a JSON:', ['items_original' => $items]);
                $items = json_encode(['descripcion' => $items], JSON_UNESCAPED_UNICODE);
            }
        }

        // Preparar datos para crear la factura (incluyendo los nuevos campos)
        $facturaData = [
            'cotizacion_id' => (int) $data['cotizacion_id'],
            'cotio_descripcion' => $data['cotio_descripcion'] ?? 'Muestra no especificada',
            'instance_number' => $data['instance_number'] ?? 0,
            'cliente_razon_social' => $cotizacion->coti_empresa ?? 'Cliente no especificado',
            'cliente_cuit' => $cotizacion->coti_cuit ?? '00-00000000-0',
            'numero_factura' => (string) $data['numero_factura'],
            'cae' => (string) $data['cae'],
            'fecha_emision' => now(),
            'fecha_vencimiento_cae' => $fechaVencimiento,
            'monto_total' => (float) $data['monto_total'],
            'items' => $items,
            'estado' => (string) $data['estado']
        ];

        Log::info('Datos preparados para crear factura:', $facturaData);

        // Crear la factura en la base de datos
        $factura = Factura::create($facturaData);

        // Marcar muestras y análisis como facturados
        $instanciaIds = array_merge(
            $data['muestras_ids'] ?? [],
            $data['analisis_ids'] ?? []
        );

        if (!empty($instanciaIds)) {
            CotioInstancia::whereIn('id', $instanciaIds)
                ->update(['facturado' => true]);

            Log::info('Instancias marcadas como facturadas:', [
                'factura_id' => $factura->id,
                'instancia_ids' => $instanciaIds
            ]);
        } else {
            Log::warning('No se encontraron IDs de muestras o análisis para marcar como facturados', [
                'factura_id' => $factura->id
            ]);
        }

        Log::info('Factura guardada exitosamente en BD:', [
            'id' => $factura->id,
            'numero_factura' => $factura->numero_factura,
            'cae' => $factura->cae,
            'monto_total' => $factura->monto_total,
            'fecha_vencimiento_cae' => $fechaVencimiento,
            'cliente_razon_social' => $factura->cliente_razon_social,
            'cliente_cuit' => $factura->cliente_cuit
        ]);

        return $factura;

    } catch (\Exception $e) {
        Log::error('Error al guardar factura en BD: ' . $e->getMessage(), [
            'data' => $data,
            'error' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        throw $e;
    }
}

private function calcularPrecioMuestra($muestra)
{
    // Usar el precio real de la base de datos (columna monto)
    $monto = $muestra->monto ?? 0;
    
    // Validar que el monto sea válido
    if ($monto <= 0) {
        Log::warning('Muestra sin monto válido, usando precio por defecto', [
            'muestra_id' => $muestra->id,
            'monto_bd' => $monto,
            'descripcion' => $muestra->cotio_descripcion
        ]);
        return 1000.00; // Precio por defecto si no hay monto en BD
    }
    
    Log::info('Usando precio real de muestra desde BD', [
        'muestra_id' => $muestra->id,
        'monto' => $monto,
        'descripcion' => $muestra->cotio_descripcion
    ]);
    
    return (float) $monto;
}

private function calcularPrecioAnalisis($analisis)
{
    // Usar el precio real de la base de datos (columna monto)
    $monto = $analisis->monto ?? 0;
    
    // Validar que el monto sea válido
    if ($monto <= 0) {
        Log::warning('Análisis sin monto válido, usando precio por defecto', [
            'analisis_id' => $analisis->id,
            'monto_bd' => $monto,
            'descripcion' => $analisis->cotio_descripcion
        ]);
        return 100.00; // Precio por defecto si no hay monto en BD
    }
    
    Log::info('Usando precio real de análisis desde BD', [
        'analisis_id' => $analisis->id,
        'monto' => $monto,
        'descripcion' => $analisis->cotio_descripcion
    ]);
    
    return (float) $monto;
}

private function integrarConArca($clienteData, $items, $montoTotal, $cotizacion)
{
    try {
        // CONFIGURACIÓN: Entorno de PRUEBA de AFIP con PRECIOS REALES de la BD
        Log::info('Integrando con AFIP en modo PRUEBA usando precios reales de BD', [
            'monto_total' => $montoTotal,
            'entorno' => 'homologacion',
            'precios' => 'reales_bd'
        ]);
        
        $afip = new Afip([
            'CUIT' => 20409378472,
            'production' => false, // Siempre en false para homologación
            'res_folder' => __DIR__ . '/afip_res',
            'debug' => true,
        ]);

        $neto = round($montoTotal / 1.21, 2);
        $iva = round($montoTotal - $neto, 2);
        $docNro = preg_replace('/\D/', '', $clienteData['cuit']);

        $facturaData = [
            'CbteTipo' => 1,
            'PtoVta' => 1,
            'Concepto' => 1,
            'DocTipo' => $clienteData['cuit'] === '00000000000' ? 99 : 80,
            'DocNro' => (float) $docNro,
            'CondicionIVAReceptorId' => 1, // Consumidor Final
            'CbteFch' => date('Ymd'),
            'ImpTotal' => number_format($montoTotal, 2, '.', ''),
            'ImpTotConc' => 0,
            'ImpNeto' => number_format($neto, 2, '.', ''),
            'ImpIVA' => number_format($iva, 2, '.', ''),
            'MonId' => 'PES',
            'MonCotiz' => 1,
            'Iva' => [
                [
                    'Id' => 5,
                    'BaseImp' => round($neto, 2),
                    'Importe' => round($iva, 2),
                ]
            ],
        ];

        $facturaData['Detalles'] = [];
        foreach ($items as $item) {
            $unitPrice = round($item['precio_unitario'] / 1.21, 2);
            $importe = round($item['subtotal'] / 1.21, 2);

            $facturaData['Detalles'][] = [
                'Qty' => (int) $item['cantidad'],
                'ProDs' => substr($item['descripcion'], 0, 250),
                'ProUMed' => 7,
                'ProPrecioUnit' => number_format($unitPrice, 2, '.', ''),
                'ProImporteItem' => number_format($importe, 2, '.', ''),
                'ProBonif' => 0,
            ];
        }

        $wsfe = $afip->ElectronicBilling;
        $result = $wsfe->CreateNextVoucher($facturaData);

        if (isset($result['CAE'], $result['voucher_number'], $result['CAEFchVto'])) {
            return [
                'success' => true,
                'numero_factura' => sprintf('%04d-%08d', $facturaData['PtoVta'], $result['voucher_number']),
                'cae' => $result['CAE'],
                'fecha_vencimiento' => $result['CAEFchVto'],
            ];
        }

        Log::error('Error al generar factura en AFIP: ' . json_encode($result));

        return [
            'success' => false,
            'error' => isset($result['Errors'])
                ? 'Error AFIP: ' . json_encode($result['Errors'])
                : 'Error al generar factura: Respuesta incompleta.',
        ];

    } catch (\Exception $e) {
        Log::error('Error en integración ARCA: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Error en integración ARCA: ' . $e->getMessage(),
        ];
    }
}

/**
 * Ver detalle de una factura específica
 */
public function verFactura($id)
{
    $factura = Factura::with('cotizacion')->findOrFail($id);
    
    // Decodificar items para mostrar en la vista
    $items = [];
    if (is_string($factura->items)) {
        $items = json_decode($factura->items, true) ?? [];
    } elseif (is_array($factura->items)) {
        $items = $factura->items;
    }

    return view('facturacion.detalle', compact('factura', 'items'));
}

// public function descargar(Factura $factura)
// {
//     try {
//         Log::info('Descargando factura', [
//             'factura_id' => $factura->id,
//             'numero_factura' => $factura->numero_factura,
//             'cae' => $factura->cae
//         ]);

//         // Verificar si ya tenemos un PDF guardado localmente
//         $fileName = 'Factura_' . str_replace(['-', '/', '\\'], '_', $factura->numero_factura) . '.pdf';
//         $filePath = storage_path('app/facturas/' . $fileName);

//         // Si el PDF ya existe, descargarlo directamente
//         if (file_exists($filePath)) {
//             Log::info('PDF encontrado en cache, descargando archivo existente', ['file' => $fileName]);
//             return response()->download($filePath, $fileName);
//         }

//         // Si no existe, generar nuevo PDF
//         Log::info('Generando nuevo PDF para factura', ['factura_id' => $factura->id]);

//         // Crear directorio si no existe
//         $directory = storage_path('app/facturas');
//         if (!file_exists($directory)) {
//             mkdir($directory, 0755, true);
//         }

//         // Generar PDF usando DomPDF
//         $items = [];
//         if (is_string($factura->items)) {
//             $items = json_decode($factura->items, true) ?? [];
//         } elseif (is_array($factura->items)) {
//             $items = $factura->items;
//         }

//         $pdf = Pdf::loadView('facturacion.template_afip', [
//             'factura' => $factura,
//             'cotizacion' => $factura->cotizacion,
//             'items' => $items,
//             'fecha' => $factura->fecha_emision->format('d/m/Y')
//         ]);

//         // Configurar opciones del PDF
//         $pdf->setPaper('A4', 'portrait');
//         $pdf->setOptions([
//             'isHtml5ParserEnabled' => true,
//             'isPhpEnabled' => true,
//             'defaultFont' => 'Arial'
//         ]);

//         // Guardar el PDF en storage
//         $pdfContent = $pdf->output();
//         file_put_contents($filePath, $pdfContent);

//         // Actualizar la referencia en la base de datos
//         $factura->update(['pdf_url' => $fileName]);

//         Log::info('PDF generado exitosamente', [
//             'factura_id' => $factura->id,
//             'archivo' => $fileName,
//             'tamaño' => strlen($pdfContent) . ' bytes'
//         ]);

//         // Descargar el archivo
//         return response()->download($filePath, $fileName);

//     } catch (\Exception $e) {
//         Log::error('Error al generar/descargar factura: ' . $e->getMessage(), [
//             'factura_id' => $factura->id,
//             'numero_factura' => $factura->numero_factura,
//             'error_trace' => $e->getTraceAsString()
//         ]);
        
//         // Devuelve JSON si es una solicitud AJAX/fetch
//         if (request()->wantsJson() || request()->ajax()) {
//             return response()->json([
//                 'error' => 'No se pudo generar el PDF: ' . $e->getMessage()
//             ], 500);
//         }
        
//         return back()->with('error', 'No se pudo generar el PDF: ' . $e->getMessage());
//     }
// }


public function descargar(Factura $factura)
{
    try {
        Log::info('Descargando factura', [
            'factura_id' => $factura->id,
            'numero_factura' => $factura->numero_factura,
            'cae' => $factura->cae
        ]);

        // Verificar si ya tenemos un PDF guardado localmente
        $fileName = 'Factura_' . str_replace(['-', '/', '\\'], '_', $factura->numero_factura) . '.pdf';
        $filePath = storage_path('app/facturas/' . $fileName);

        if (file_exists($filePath)) {
            Log::info('PDF encontrado en cache, descargando archivo existente', ['file' => $fileName]);
            return response()->download($filePath, $fileName);
        }

        // Crear directorio si no existe
        $directory = storage_path('app/facturas');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Cargar el archivo bill.html
        $htmlPath = base_path('resources/views/facturacion/bill.html');
        if (!file_exists($htmlPath)) {
            throw new \Exception('Archivo bill.html no encontrado en ' . $htmlPath);
        }
        $html = file_get_contents($htmlPath);

        // Preparar los datos para reemplazar los placeholders
        $items = is_string($factura->items) ? json_decode($factura->items, true) ?? [] : $factura->items;
        $total = $factura->monto_total ?? 0;
        $neto = round($total / 1.21, 2);
        $iva = round($total - $neto, 2);

        // Generar la tabla de ítems
        $itemsTable = '';
        if ($items && is_array($items)) {
            foreach ($items as $index => $item) {
                $descripcion = htmlspecialchars($item['descripcion'] ?? 'N/A');
                $identificacion = isset($item['identificacion']) && $item['identificacion'] ? "<br><small style=\"color: #666;\">ID: " . htmlspecialchars($item['identificacion']) . "</small>" : '';
                $resultado = isset($item['resultado']) && $item['resultado'] ? "<br><small style=\"color: #666;\">Resultado: " . htmlspecialchars($item['resultado']) . "</small>" : '';
                $tipo = $item['tipo'] == 'muestra' ? 'Muestra' : ($item['tipo'] == 'analisis' ? 'Análisis' : 'Unidad');
                $itemsTable .= "
                    <tr>
                        <td>" . ($index + 1) . "</td>
                        <td>$descripcion$identificacion$resultado</td>
                        <td>" . ($item['cantidad'] ?? 1) . "</td>
                        <td>$tipo</td>
                        <td>" . number_format($item['precio_unitario'] ?? 0, 2, ',', '.') . "</td>
                        <td>0,00</td>
                        <td>0,00</td>
                        <td>" . number_format($item['subtotal'] ?? 0, 2, ',', '.') . "</td>
                    </tr>";
            }
        } else {
            $itemsTable = '<tr><td colspan="8" style="text-align: center;">No hay ítems registrados</td></tr>';
        }

        // Reemplazar placeholders
        $replacements = [
            '{{numero_factura}}' => htmlspecialchars($factura->numero_factura ?? 'N/A'),
            '{{empresa_nombre}}' => htmlspecialchars(env('EMPRESA_NOMBRE', 'Industria y Ambiente')),
            '{{empresa_direccion}}' => htmlspecialchars(env('EMPRESA_DIRECCION', 'Dirección del Laboratorio')),
            '{{empresa_cuit}}' => htmlspecialchars(env('EMPRESA_CUIT', '20-12345678-9')),
            '{{empresa_iibb}}' => htmlspecialchars(env('EMPRESA_IIBB', '12345432')),
            '{{empresa_fecha_inicio}}' => htmlspecialchars(env('EMPRESA_FECHA_INICIO', '01/01/2020')),
            '{{punto_venta}}' => htmlspecialchars(substr($factura->numero_factura ?? '0000-00000000', 0, 4)),
            '{{comp_nro}}' => htmlspecialchars(substr($factura->numero_factura ?? '0000-00000000', 5)),
            '{{fecha_emision}}' => $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y') : 'N/A',
            '{{periodo_desde}}' => $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y') : 'N/A',
            '{{periodo_hasta}}' => $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y') : 'N/A',
            '{{fecha_vencimiento_cae}}' => $factura->fecha_vencimiento_cae ? \Carbon\Carbon::parse($factura->fecha_vencimiento_cae)->format('d/m/Y') : 'N/A',
            '{{coti_cuit}}' => htmlspecialchars($factura->cotizacion->coti_cuit ?? 'N/A'),
            '{{coti_empresa}}' => htmlspecialchars($factura->cotizacion->coti_empresa ?? 'N/A'),
            '{{coti_direccioncli}}' => htmlspecialchars($factura->cotizacion->coti_direccioncli ?? 'N/A'),
            '{{items_table}}' => $itemsTable,
            '{{neto}}' => number_format($neto, 2, ',', '.'),
            '{{iva}}' => number_format($iva, 2, ',', '.'),
            '{{total}}' => number_format($total, 2, ',', '.'),
            '{{cae}}' => htmlspecialchars($factura->cae ?? 'N/A'),
            '{{qr_code}}' => htmlspecialchars($factura->qr_code ?? '')
        ];
        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Log HTML para depuración
        file_put_contents(storage_path('app/debug_bill.html'), $html);
        Log::debug('Processed HTML saved to storage/app/debug_bill.html');

        // Opciones para el archivo
        $options = [
            'width' => 8,
            'marginLeft' => 0.4,
            'marginRight' => 0.4,
            'marginTop' => 0.4,
            'marginBottom' => 0.4
        ];

        // Crear PDF con Afip SDK
        $afip = new Afip(['CUIT' => env('AFIP_CUIT'), 'production' => env('AFIP_PRODUCTION', false)]);
        try {
            $res = $afip->ElectronicBilling->CreatePDF([
                'html' => $html,
                'file_name' => $fileName,
                'options' => $options
            ]);
            Log::debug('CreatePDF response:', ['response' => $res]);
        } catch (\Exception $e) {
            throw new \Exception('Error en CreatePDF: ' . $e->getMessage());
        }

        // Verificar si el archivo es una URL
        if (!isset($res['file'])) {
            throw new \Exception('No se generó el archivo PDF. Respuesta: ' . json_encode($res));
        }

        // Si es una URL, descargar el archivo
        if (filter_var($res['file'], FILTER_VALIDATE_URL)) {
            $pdfContent = @file_get_contents($res['file']);
            if ($pdfContent === false) {
                throw new \Exception('No se pudo descargar el PDF desde ' . $res['file']);
            }
            if (!file_put_contents($filePath, $pdfContent)) {
                throw new \Exception('No se pudo guardar el PDF en ' . $filePath);
            }
        } else {
            // Si es un archivo local, moverlo
            if (!file_exists($res['file'])) {
                throw new \Exception('El archivo PDF local no existe: ' . $res['file']);
            }
            if (!rename($res['file'], $filePath)) {
                throw new \Exception('No se pudo mover el archivo PDF de ' . $res['file'] . ' a ' . $filePath);
            }
        }

        // Actualizar la referencia en la base de datos
        $factura->update(['pdf_url' => $fileName]);

        Log::info('PDF generado exitosamente con Afip SDK', [
            'factura_id' => $factura->id,
            'archivo' => $fileName,
            'tamaño' => filesize($filePath) . ' bytes'
        ]);

        return response()->download($filePath, $fileName);

    } catch (\Exception $e) {
        Log::error('Error al generar/descargar factura: ' . $e->getMessage(), [
            'factura_id' => $factura->id,
            'numero_factura' => $factura->numero_factura,
            'error_trace' => $e->getTraceAsString()
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'error' => 'No se pudo generar el PDF: ' . $e->getMessage()
            ], 500);
        }

        return back()->with('error', 'No se pudo generar el PDF: ' . $e->getMessage());
    }
}


private function urlPdfValida($url)
{
    try {
        $headers = get_headers($url);
        return strpos($headers[0], '200') !== false;
    } catch (\Exception $e) {
        return false;
    }
}





}
