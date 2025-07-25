<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\FacadesLog;


use App\Models\Coti;
use App\Models\Cotio;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\InventarioLab;
use App\Models\CotioResponsable;
use App\Models\InventarioMuestreo;
use App\Models\CotioInstancia;
use App\Models\CotioInventarioMuestreo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\CotioValorVariable;

class CotioController extends Controller
{



public function updateFechaCarga(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required|string',
        'cotio_item' => 'required|integer',
        'cotio_subitem' => 'required|integer',
        'instance_number' => 'required|integer',
        'fecha_carga_ot' => 'required|date'
    ]);

    $instancia = CotioInstancia::where([
        'cotio_numcoti' => $request->cotio_numcoti,
        'cotio_item' => $request->cotio_item,
        'cotio_subitem' => $request->cotio_subitem,
        'instance_number' => $request->instance_number
    ])->firstOrFail();
    
    $instancia->fecha_carga_ot = $request->fecha_carga_ot;
    $instancia->save();

    return response()->json([
        'success' => true,
        'message' => 'Fecha de carga actualizada correctamente'
    ]);
}

public function pasarMuestreo(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required|string',
        'cambios' => 'required|array'
    ]);

    try {
        DB::beginTransaction();

        $updatedCount   = 0;
        $failedUpdates  = [];
        $userId         = Auth::user()->usu_codigo;
        $cotioNumcoti   = $request->cotio_numcoti;
        $cambios        = $request->cambios;

        // 1) Precarga descripciones de categorías (subitem = 0) y de análisis (subitem > 0)
        $allItems = Cotio::where('cotio_numcoti', $cotioNumcoti)
            ->get()
            ->groupBy('cotio_subitem'); // 0 ⇒ muestras; >0 ⇒ análisis

        foreach ($cambios as $key => $activado) {
            if (!preg_match('/^(\d+)-(\d+)-(\d+)$/', $key, $m)) {
                throw new \Exception("Formato inválido para key: {$key}");
            }
            [, $item, $subitem, $instance] = $m;
        
            // 1) Instancia de muestra
            if ($subitem == 0) {
                $instancia = CotioInstancia::firstOrNew([
                    'cotio_numcoti'   => $cotioNumcoti,
                    'cotio_item'      => $item,
                    'cotio_subitem'   => 0,
                    'instance_number' => $instance,
                ]);

                // Obtener descripción para muestra (subitem = 0)
                $descripcion = $allItems->get(0)?->firstWhere('cotio_item', $item)?->cotio_descripcion;
                
                if ($descripcion && !$instancia->cotio_descripcion) {
                    $instancia->cotio_descripcion = $descripcion;
                }
                
                $instancia->active_muestreo = $activado;
                // si requiere fecha/coordinador:
                if ($activado) {
                    $instancia->fecha_muestreo     = now();
                    $instancia->coordinador_codigo = $userId;
                } else {
                    $instancia->fecha_muestreo     = null;
                    $instancia->coordinador_codigo = null;
                }
        
                $instancia->save();
                $updatedCount++;
        
                // Si activé la muestra, creo/copio todos los análisis de catálogo
                if ($activado) {
                    $analisis = collect($allItems)
                        ->filter(fn($group, $sub) => $sub > 0)
                        ->flatten(1)
                        ->where('cotio_item', $item);
        
                    foreach ($analisis as $tarea) {
                        $analisisKey = "{$item}-{$tarea->cotio_subitem}-{$instance}";
                        $checked     = !empty($cambios[$analisisKey]);
        
                        $instAn = CotioInstancia::firstOrNew([
                            'cotio_numcoti'   => $cotioNumcoti,
                            'cotio_item'      => $item,
                            'cotio_subitem'   => $tarea->cotio_subitem,
                            'instance_number' => $instance,
                        ]);
        
                        // Asignar descripción del análisis
                        $descAnalisis = $tarea->cotio_descripcion;
                        if ($descAnalisis && !$instAn->cotio_descripcion) {
                            $instAn->cotio_descripcion = $descAnalisis;
                        }
        
                        $instAn->active_muestreo = $checked;
                        if ($checked) {
                            $instAn->fecha_muestreo     = now();
                            $instAn->coordinador_codigo = $userId;
                        }
        
                        $instAn->save();
                        $updatedCount++;
                    }
                }
        
            // 2) Instancia de análisis individual (actualización posterior)
            } else {
                $instAn = CotioInstancia::firstOrNew([
                    'cotio_numcoti'   => $cotioNumcoti,
                    'cotio_item'      => $item,
                    'cotio_subitem'   => $subitem,
                    'instance_number' => $instance,
                ]);
        
                // Obtener descripción para análisis (subitem > 0)
                $descripcion = $allItems->get($subitem)?->firstWhere('cotio_item', $item)?->cotio_descripcion;
                
                if ($descripcion && !$instAn->cotio_descripcion) {
                    $instAn->cotio_descripcion = $descripcion;
                }
                
                $instAn->active_muestreo = $activado;
                if ($activado) {
                    $instAn->fecha_muestreo     = now();
                    $instAn->coordinador_codigo = $userId;
                } else {
                    $instAn->fecha_muestreo     = null;
                    $instAn->coordinador_codigo = null;
                }
        
                $instAn->save();
                $updatedCount++;
            }
        }

        DB::commit();

        return response()->json([
            'success'       => true,
            'message'       => "{$updatedCount} registros creados/actualizados correctamente",
            'updated_count' => $updatedCount,
            'failed_updates'=> $failedUpdates
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success'       => false,
            'message'       => 'Error: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ], 500);
    }
}
    
    
protected function actualizarContadorMuestra($cotioNumcoti, $item, $cantidadTotal)
{
    $muestreadas = CotioInstancia::where('cotio_numcoti', $cotioNumcoti)
        ->where('cotio_item', $item)
        ->where('cotio_subitem', 0)
        ->whereNotNull('fecha_muestreo')
        ->count();
    
    Cotio::where('cotio_numcoti', $cotioNumcoti)
        ->where('cotio_item', $item)
        ->where('cotio_subitem', 0)
        ->update([
            'muestreo_contador' => "$muestreadas/$cantidadTotal",
            'enable_muestreo' => $muestreadas > 0
        ]);
}


public function asignarSuspensionMuestra(Request $request)
{
    $validated = $request->validate([
        'cotio_numcoti' => 'required|string',
        'cotio_item' => 'required|integer',
        'instance_number' => 'required|integer',
        'cotio_observaciones_suspension' => 'required|string|max:500',
    ]);

    try {
        $instancia = CotioInstancia::where([
            'cotio_numcoti' => $validated['cotio_numcoti'],
            'cotio_item' => $validated['cotio_item'],
            'cotio_subitem' => 0,
            'instance_number' => $validated['instance_number']
        ])->firstOrFail();

        $instancia->update([
            'cotio_observaciones_suspension' => $validated['cotio_observaciones_suspension'],
            'cotio_estado' => 'suspension',
        ]);


        return redirect()->back()->with('success', 'La muestra ha sido suspendida correctamente.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Ocurrió un error al suspender la muestra: ' . $e->getMessage())
            ->withInput();
    }
}



    
public function asignarDetalles(Request $request)
{
    $validated = $request->validate([
        'cotio_numcoti' => 'required|string',
        'cotio_item' => 'required|integer',
        'instance' => 'required|integer',
        'vehiculo_asignado' => 'sometimes|nullable|integer',
        'responsable_codigo' => 'sometimes|nullable|string',
        'fecha_inicio_muestreo' => 'sometimes|nullable|date',
        'fecha_fin_muestreo' => 'sometimes|nullable|date|after_or_equal:fecha_inicio_muestreo',
        'herramientas' => 'sometimes|nullable|array',
        'herramientas.*' => 'sometimes|integer',
        'tareas_seleccionadas' => 'sometimes|nullable|array',
        'tareas_seleccionadas.*' => 'sometimes|string'
    ]);

    DB::beginTransaction();
    try {
        $instanciaActual = CotioInstancia::firstOrNew([
            'cotio_numcoti' => $validated['cotio_numcoti'],
            'cotio_item' => $validated['cotio_item'],
            'cotio_subitem' => 0,
            'instance_number' => $validated['instance']
        ]);

        $updateData = [];
        
        if ($request->has('vehiculo_asignado')) {
            $updateData['vehiculo_asignado'] = $validated['vehiculo_asignado'];
        }
        
        if ($request->filled('responsable_codigo')) {
            $instanciaActual->responsable_muestreo = $validated['responsable_codigo'] === 'NULL' ? null : $validated['responsable_codigo'];
        }
        
        if ($request->has('fecha_inicio_muestreo')) {
            $updateData['fecha_inicio_muestreo'] = $validated['fecha_inicio_muestreo'];
        }
        
        if ($request->has('fecha_fin_muestreo')) {
            $updateData['fecha_fin_muestreo'] = $validated['fecha_fin_muestreo'];
        }

        // Actualizar solo si hay datos para actualizar
        if (!empty($updateData)) {
            $instanciaActual->fill($updateData)->save();
        }

        // Actualizar herramientas solo si vienen en la solicitud
        if ($request->has('herramientas')) {
            $this->actualizarHerramientas(
                $validated['cotio_numcoti'],
                $validated['cotio_item'],
                0, 
                $validated['instance'],
                $validated['herramientas']
            );
        }

        // Actualizar análisis seleccionados
        if ($request->has('tareas_seleccionadas') && !empty($validated['tareas_seleccionadas'])) {
            foreach ($validated['tareas_seleccionadas'] as $tarea) {
                [$item, $subitem] = explode('_', $tarea);
                
                $instanciaAnalisis = CotioInstancia::firstOrNew([
                    'cotio_numcoti' => $validated['cotio_numcoti'],
                    'cotio_item' => $item,
                    'cotio_subitem' => $subitem,
                    'instance_number' => $validated['instance']
                ]);

                // Solo actualizar los campos que vienen en la solicitud
                $updateAnalisisData = [];
                
                if ($request->has('vehiculo_asignado')) {
                    $updateAnalisisData['vehiculo_asignado'] = $validated['vehiculo_asignado'];
                }
                
                if ($request->filled('responsable_codigo')) {
                    $updateAnalisisData['responsable_muestreo'] = $validated['responsable_codigo'] === 'NULL' ? null : $validated['responsable_codigo'];
                }
                
                if ($request->has('fecha_inicio_muestreo')) {
                    $updateAnalisisData['fecha_inicio_muestreo'] = $validated['fecha_inicio_muestreo'];
                }
                
                if ($request->has('fecha_fin_muestreo')) {
                    $updateAnalisisData['fecha_fin_muestreo'] = $validated['fecha_fin_muestreo'];
                }

                if (!empty($updateAnalisisData)) {
                    $instanciaAnalisis->fill($updateAnalisisData)->save();
                }

                // Actualizar herramientas solo si vienen en la solicitud
                if ($request->has('herramientas')) {
                    $this->actualizarHerramientas(
                        $validated['cotio_numcoti'],
                        $item,
                        $subitem,
                        $validated['instance'],
                        $validated['herramientas']
                    );
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Detalles asignados correctamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al asignar detalles: ' . $e->getMessage()
        ], 500);
    }
}



protected function actualizarHerramientas($cotioNumcoti, $cotioItem, $cotioSubitem, $instanceNumber, $herramientasSeleccionadas)
{
    CotioInventarioMuestreo::where([
        'cotio_numcoti' => $cotioNumcoti,
        'cotio_item' => $cotioItem,
        'cotio_subitem' => $cotioSubitem,
        'instance_number' => $instanceNumber
    ])->delete();

    foreach ($herramientasSeleccionadas as $herramientaId) {
        DB::table('cotio_inventario_muestreo')->insert([
            'cotio_numcoti' => $cotioNumcoti,
            'cotio_item' => $cotioItem,
            'cotio_subitem' => $cotioSubitem,
            'instance_number' => $instanceNumber,
            'inventario_muestreo_id' => $herramientaId,
            'cantidad' => 1,
            'observaciones' => null
        ]);
    }
}






public function asignarFrecuencia(Request $request) 
{
    $request->validate([
        'cotio_numcoti' => 'required',
        'cotio_item' => 'required',
        'cotio_subitem' => 'required|numeric',
        'es_frecuente' => 'required|boolean',
        'frecuencia_dias' => 'required|string|in:diario,semanal,quincenal,mensual,trimestral,cuatr,semestral,anual',
        'tareas_seleccionadas' => 'nullable|array'
    ]);

    try {
        DB::beginTransaction();

        // Actualizar la categoría
        $categoria = Cotio::where('cotio_numcoti', $request->cotio_numcoti)
                        ->where('cotio_item', $request->cotio_item)
                        ->where('cotio_subitem', 0) 
                        ->firstOrFail();

        $categoria->es_frecuente = $request->es_frecuente;
        $categoria->frecuencia_dias = $request->frecuencia_dias;
        $categoria->save();

        // Actualizar las tareas seleccionadas si existen
        if ($request->has('tareas_seleccionadas') && !empty($request->tareas_seleccionadas)) {
            foreach ($request->tareas_seleccionadas as $tarea) {
                $tareaModel = Cotio::where('cotio_numcoti', $request->cotio_numcoti)
                                ->where('cotio_item', $tarea['item'])
                                ->where('cotio_subitem', $tarea['subitem'])
                                ->first();

                if ($tareaModel) {
                    $tareaModel->es_frecuente = $request->es_frecuente;
                    $tareaModel->frecuencia_dias = $request->frecuencia_dias;
                    $tareaModel->save();
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true, 
            'message' => 'Frecuencia actualizada correctamente'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al asignar frecuencia: ' . $e->getMessage(),
            'error_details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
}







public function asignarResponsableTareaIndividual(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required',
        'cotio_item' => 'required',
        'cotio_subitem' => 'required',
        'usuario_id' => 'required|exists:usu,usu_codigo'
    ]);

    try {
        $tarea = Cotio::where('cotio_numcoti', $request->cotio_numcoti)
                     ->where('cotio_item', $request->cotio_item)
                     ->where('cotio_subitem', $request->cotio_subitem)
                     ->firstOrFail();

        $tarea->cotio_responsable_codigo = $request->usuario_id;
        $tarea->save();

        return response()->json(['success' => true, 'message' => 'Responsable asignado correctamente']);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al asignar responsable: ' . $e->getMessage()], 500);
    }
}

// admin
public function asignarIdentificacion(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required',
        'cotio_item' => 'required',
        'cotio_subitem' => 'nullable',
        'cotio_identificacion' => 'nullable|string|max:255',
        'volumen_muestra' => 'nullable|numeric|min:0',
        'cotio_obs' => 'nullable|string|max:255',
    ]);

    $categoria = Cotio::where('cotio_numcoti', $request->cotio_numcoti)
                    ->where('cotio_item', $request->cotio_item)
                    ->where('cotio_subitem', 0)
                    ->firstOrFail();

    $categoria->cotio_identificacion = $request->cotio_identificacion;
    $categoria->volumen_muestra = $request->volumen_muestra;
    $categoria->cotio_obs = $request->cotio_obs;
    $categoria->save();

    return response()->json([
        'success' => true,
        'message' => 'Identificación y observaciones guardadas correctamente'
    ]);
}

// user
public function asignarIdentificacionMuestra(Request $request)
{
    try {
        // Validación
        $validated = $request->validate([
            'cotio_identificacion' => 'nullable|string|max:255',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'cotio_numcoti' => 'required',
            'cotio_item' => 'required',
            'instance_number' => 'required',
            'image_base64' => 'nullable|string',
            'remove_image' => 'nullable|boolean'
        ]);

        Log::info('Datos recibidos:', $request->all());

        $instancia = CotioInstancia::where([
            'cotio_numcoti' => $request->cotio_numcoti,
            'cotio_item' => $request->cotio_item,
            'cotio_subitem' => 0,
            'instance_number' => $request->instance_number
        ])->firstOrFail();

        $data = [
            'cotio_identificacion' => $request->cotio_identificacion,
            'cotio_estado' => 'en revision muestreo'
        ];

        try {
            // Procesamiento de coordenadas
            if ($request->filled('latitud') && $request->filled('longitud')) {
                $lat = (float)$request->latitud;
                $lng = (float)$request->longitud;
                
                Log::info('Coordenadas procesadas:', ['lat' => $lat, 'lng' => $lng]);
                
                $data['cotio_georef'] = "{$lat}, {$lng}";
                $data['latitud'] = $lat;
                $data['longitud'] = $lng;
            } else {
                // Si no vienen coordenadas, limpiar los campos
                $data['cotio_georef'] = null;
                $data['latitud'] = null;
                $data['longitud'] = null;
            }

            // Manejo de imagen
            if ($request->has('remove_image') && $request->remove_image && $instancia->image) {
                Storage::delete('public/images/' . $instancia->image);
                $data['image'] = null;
            }

            if ($request->filled('image_base64')) {
                try {
                    // Decodificar la imagen base64
                    $image_parts = explode(";base64,", $request->image_base64);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    
                    // Generar nombre único para la imagen
                    $imageName = 'muestra_'.$instancia->id.'_'.time().'.'.$image_type;
                    
                    // Asegurarse de que el directorio existe
                    if (!Storage::disk('public')->exists('images')) {
                        Storage::disk('public')->makeDirectory('images');
                    }
                    
                    // Guardar la imagen
                    $path = Storage::disk('public')->put('images/'.$imageName, $image_base64);
                    
                    if (!$path) {
                        throw new \Exception("No se pudo guardar la imagen");
                    }
                    
                    $data['image'] = $imageName;
                    
                    Log::info('Imagen guardada exitosamente:', [
                        'path' => $path,
                        'name' => $imageName
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al procesar la imagen:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    throw new \Exception("Error al procesar la imagen: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Error crítico al guardar imagen:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la imagen: '.$e->getMessage()
            ], 500);
        }

        Log::info('Datos a actualizar:', $data);
        
        $instancia->update($data);
        
        Log::info('Registro actualizado:', $instancia->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Datos actualizados correctamente',
            'redirect' => url()->previous()
        ]);

    } catch (\Exception $e) {
        Log::error('Error al actualizar muestra:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error: ' . $e->getMessage()
        ], 500);
    }
}




// public function verCategoria($cotizacion, $item, $instance = null)
// {
//     $cotizacion = Coti::findOrFail($cotizacion);
//     $instance = $instance ?? 1;
    
//     // Obtener la muestra principal
//     $categoria = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
//                 ->where('cotio_item', $item)
//                 ->where('cotio_subitem', 0)
//                 ->firstOrFail();
    
//     // Obtener la instancia de la muestra
//     $instanciaMuestra = CotioInstancia::where([
//                     'cotio_numcoti' => $cotizacion->coti_num,
//                     'cotio_item' => $item,
//                     'cotio_subitem' => 0,
//                     'instance_number' => $instance,
//                     'active_muestreo' => true
//                 ])->first();
    
//     // Obtener herramientas manualmente para la instancia de muestra
//     if ($instanciaMuestra) {
//         $herramientasMuestra = DB::table('cotio_inventario_muestreo')
//             ->where('cotio_numcoti', $instanciaMuestra->cotio_numcoti)
//             ->where('cotio_item', $instanciaMuestra->cotio_item)
//             ->where('cotio_subitem', $instanciaMuestra->cotio_subitem)
//             ->where('instance_number', $instanciaMuestra->instance_number)
//             ->join('inventario_muestreo', 'cotio_inventario_muestreo.inventario_muestreo_id', '=', 'inventario_muestreo.id')
//             ->select(
//                 'inventario_muestreo.*',
//                 'cotio_inventario_muestreo.cantidad',
//                 'cotio_inventario_muestreo.observaciones as pivot_observaciones'
//             )
//             ->get();
            
//         $instanciaMuestra->herramientas = $herramientasMuestra;
//     }
    
//     if (!$instanciaMuestra) {
//         return view('cotizaciones.tareasporcategoria', [
//             'cotizacion' => $cotizacion,
//             'categoria' => $categoria,
//             'tareas' => collect(),
//             'usuarios' => collect(),
//             'inventario' => collect(),
//             'instance' => $instance,
//             'instanciaActual' => null, 
//             'instanciasMuestra' => collect()
//         ]);
//     }

//     // Obtener tareas (análisis)
//     $tareas = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
//                 ->where('cotio_item', $item)
//                 ->where('cotio_subitem', '!=', 0)
//                 ->orderBy('cotio_subitem')
//                 ->get();
    
//     $tareasConInstancias = $tareas->map(function($tarea) use ($instance) {
//         $instancia = CotioInstancia::where([
//             'cotio_numcoti' => $tarea->cotio_numcoti,
//             'cotio_item' => $tarea->cotio_item,
//             'cotio_subitem' => $tarea->cotio_subitem,
//             'instance_number' => $instance,
//             'active_muestreo' => true
//         ])->first();
        
//         if ($instancia) {
//             // Obtener herramientas manualmente para cada análisis
//             $herramientasAnalisis = DB::table('cotio_inventario_muestreo')
//                 ->where('cotio_numcoti', $instancia->cotio_numcoti)
//                 ->where('cotio_item', $instancia->cotio_item)
//                 ->where('cotio_subitem', $instancia->cotio_subitem)
//                 ->where('instance_number', $instancia->instance_number)
//                 ->join('inventario_muestreo', 'cotio_inventario_muestreo.inventario_muestreo_id', '=', 'inventario_muestreo.id')
//                 ->select(
//                     'inventario_muestreo.*',
//                     'cotio_inventario_muestreo.cantidad',
//                     'cotio_inventario_muestreo.observaciones as pivot_observaciones'
//                 )
//                 ->get();
                
//             $instancia->herramientas = $herramientasAnalisis;
//             $tarea->instancia = $instancia;
//             return $tarea;
//         }
//         return null;
//     })->filter();
    
//     $usuarios = User::where('usu_nivel', '<=', 500)
//                 ->orderBy('usu_descripcion')
//                 ->get();
    
//     $inventario = InventarioMuestreo::all();
//     $vehiculos = Vehiculo::all();
    
//     $instanciasMuestra = CotioInstancia::where('cotio_numcoti', $cotizacion->coti_num)
//                             ->where('cotio_item', $item)
//                             ->where('cotio_subitem', 0)
//                             ->where('active_muestreo', true)
//                             ->get()
//                             ->keyBy('instance_number');
    
//     return view('cotizaciones.tareasporcategoria', [
//         'cotizacion' => $cotizacion,
//         'categoria' => $categoria,
//         'tareas' => $tareasConInstancias,
//         'usuarios' => $usuarios,
//         'inventario' => $inventario,
//         'instance' => $instance,
//         'vehiculos' => $vehiculos,
//         'instanciaActual' => $instanciaMuestra, 
//         'instanciasMuestra' => $instanciasMuestra
//     ]);
// }






public function desasignarHerramienta($cotizacion, $item, $subitem, $herramienta_id)
{
    $tarea = Cotio::where('cotio_numcoti', $cotizacion)
                  ->where('cotio_item', $item)
                  ->where('cotio_subitem', $subitem)
                  ->firstOrFail();

    DB::table('cotio_inventario_lab')
        ->where('cotio_numcoti', $cotizacion)
        ->where('cotio_item', $item)
        ->where('cotio_subitem', $subitem)
        ->where('inventario_lab_id', $herramienta_id) 
        ->delete();

    InventarioLab::where('id', $herramienta_id)
                 ->update(['estado' => 'libre']);

    return redirect()->back()->with('success', 'Herramienta desasignada correctamente.');
}

public function desasignarVehiculo($cotizacion, $item, $subitem, $vehiculo_id)
{
    $tarea = Cotio::where('cotio_numcoti', $cotizacion)
                  ->where('cotio_item', $item)
                  ->where('cotio_subitem', $subitem)
                  ->firstOrFail();

    DB::table('cotio')
        ->where('cotio_numcoti', $cotizacion)
        ->where('cotio_item', $item)
        ->where('cotio_subitem', $subitem)
        ->where('vehiculo_asignado', $vehiculo_id)
        ->update(['vehiculo_asignado' => null]);

    Vehiculo::where('id', $vehiculo_id)
            ->update(['estado' => 'libre']);

    return redirect()->back()->with('success', 'Vehículo desasignado correctamente.');
}




public function updateEstado(Request $request, $cotio_numcoti, $cotio_item, $cotio_subitem)
{
    $request->validate([
        'nuevo_estado' => 'required|in:pendiente,en proceso,finalizado',
    ]);

    try {
        $userCodigo = trim(Auth::user()->usu_codigo);

        $tarea = Cotio::where('cotio_numcoti', $cotio_numcoti)
                      ->where('cotio_item', $cotio_item)
                      ->where('cotio_subitem', $cotio_subitem)
                      ->firstOrFail();

        if (trim($tarea->cotio_responsable_codigo) !== $userCodigo) {
            abort(403, 'No autorizado');
        }

        if ($request->nuevo_estado === 'finalizado' && $tarea->vehiculo_asignado) {
            $vehiculo = Vehiculo::find($tarea->vehiculo_asignado);
            if ($vehiculo) {
                $vehiculo->estado = Vehiculo::ESTADO_LIBRE;
                $vehiculo->save();
            }
            $tarea->vehiculo_asignado = null;
        }

        $tarea->cotio_estado = $request->nuevo_estado;
        $tarea->save();

        Cotio::actualizarEstadoCategoria($cotio_numcoti, $cotio_item);

        return redirect()->back()->with('success', 'Estado actualizado correctamente');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
    }
}



public function actualizarEstado(Request $request)
{
    $validated = $request->validate([
        'cotio_numcoti' => 'required|numeric',
        'cotio_item' => 'required|numeric',
        'cotio_subitem' => 'required|numeric',
        'instance_number' => 'required|numeric',
        'estado' => 'required|in:coordinado analisis,en revision analisis,analizado,suspension,coordinado muestreo,en revision muestreo,muestreado',
    ]);

    try {
        DB::beginTransaction();

        $item = CotioInstancia::where([
            'cotio_numcoti' => $validated['cotio_numcoti'],
            'cotio_item' => $validated['cotio_item'],
            'cotio_subitem' => $validated['cotio_subitem'],
            'instance_number' => $validated['instance_number']
        ])->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Elemento no encontrado'
            ], 404);
        }

        $vehiculoAsignado = $item->vehiculo_asignado;

        if(Auth::user()->rol == 'coordinador_muestreo' || Auth::user()->usu_nivel >= '900') {
            $item->cotio_estado = $validated['estado'];
        } 

        if ($validated['estado'] === 'finalizado') {
            if (empty($item->fecha_fin)) {
                $item->fecha_fin = now();
            }
            
            if ($vehiculoAsignado) {
                $item->vehiculo_asignado = null;
                
                Vehiculo::where('id', $vehiculoAsignado)
                    ->update(['estado' => 'libre']);
            }

            $herramientasAsignadas = DB::table('cotio_inventario_muestreo')
                ->where('cotio_numcoti', $validated['cotio_numcoti'])
                ->where('cotio_item', $validated['cotio_item'])
                ->where('cotio_subitem', $validated['cotio_subitem'])
                ->where('instance_number', $validated['instance_number'])
                ->pluck('inventario_muestreo_id');

            if ($herramientasAsignadas->isNotEmpty()) {
                DB::table('cotio_inventario_muestreo')
                    ->where('cotio_numcoti', $validated['cotio_numcoti'])
                    ->where('cotio_item', $validated['cotio_item'])
                    ->where('cotio_subitem', $validated['cotio_subitem'])
                    ->where('instance_number', $validated['instance_number'])
                    ->delete();

                InventarioMuestreo::whereIn('id', $herramientasAsignadas)
                    ->update(['estado' => 'libre']);
            }
        }

        $item->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error en actualizarEstado: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar el estado: ' . $e->getMessage()
        ], 500);
    }
}

protected function actualizarEstadoCategoriaPadre($numcoti, $item, $instanceNumber)
{
    // Verificar el estado de todas las tareas de esta categoría
    $tareas = CotioInstancia::where([
        'cotio_numcoti' => $numcoti,
        'cotio_item' => $item,
        'instance_number' => $instanceNumber
    ])->where('cotio_subitem', '>', 0)->get();

    if ($tareas->isEmpty()) return;

    $todosFinalizados = $tareas->every(fn($t) => $t->cotio_estado === 'finalizado');
    $algunoEnProceso = $tareas->contains(fn($t) => $t->cotio_estado === 'en proceso');

    $categoria = CotioInstancia::where([
        'cotio_numcoti' => $numcoti,
        'cotio_item' => $item,
        'cotio_subitem' => 0,
        'instance_number' => $instanceNumber
    ])->first();

    if ($todosFinalizados) {
        $categoria->cotio_estado = 'finalizado';
    } elseif ($algunoEnProceso) {
        $categoria->cotio_estado = 'en proceso';
    } else {
        $categoria->cotio_estado = 'pendiente';
    }

    $categoria->save();
}




public function updateResultado(Request $request, $cotio_numcoti, $cotio_item, $cotio_subitem, $instance)
{
    $request->validate([
        'resultado' => 'nullable|string|max:255',
        'resultado_2' => 'nullable|string|max:255',
        'resultado_3' => 'nullable|string|max:255',
        'resultado_final' => 'nullable|string|max:255',
        'valores' => 'nullable|array',
        'observacion_resultado' => 'nullable|string|max:255',
        'observacion_resultado_2' => 'nullable|string|max:255',
        'observacion_resultado_3' => 'nullable|string|max:255',
        'observacion_resultado_final' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        // Encontrar la instancia
        $instancia = CotioInstancia::where([
            'cotio_numcoti' => $cotio_numcoti,
            'cotio_item' => $cotio_item,
            'cotio_subitem' => $cotio_subitem,
            'instance_number' => $instance,
        ])->firstOrFail();

        // Determinar si hay cambios que requieran cambiar el estado
        $hasResultado = $request->filled('resultado') || $request->filled('resultado_2') || $request->filled('resultado_3') || $request->filled('resultado_final');

        // Actualizar estado si hay resultados
        if ($hasResultado) {
            if(Auth::user()->rol == 'muestreador') {
                $instancia->cotio_estado = 'en revision muestreo';
            } else {
                $instancia->cotio_estado_analisis = 'en revision analisis';
                $muestra = CotioInstancia::where([
                    'cotio_numcoti' => $cotio_numcoti,
                    'cotio_item' => $cotio_item,
                    'cotio_subitem' => 0,
                    'instance_number' => $instance,
                ])->firstOrFail();
                $muestra->cotio_estado_analisis = 'en revision analisis';
                $muestra->save();
            }
        }

        // Actualizar resultados
        if($request->filled('resultado')) {
            $instancia->resultado = $request->resultado;
            $instancia->responsable_resultado_1 = Auth::user()->usu_codigo;
        }
        if($request->filled('resultado_2')) {
            $instancia->resultado_2 = $request->resultado_2;
            $instancia->responsable_resultado_2 = Auth::user()->usu_codigo;
        }
        if($request->filled('resultado_3')) {
            $instancia->resultado_3 = $request->resultado_3;
            $instancia->responsable_resultado_3 = Auth::user()->usu_codigo;
        }
        if($request->filled('resultado_final')) {
            $instancia->resultado_final = $request->resultado_final;
            $instancia->responsable_resultado_final = Auth::user()->usu_codigo;
        }

        // Actualizar observaciones
        if($request->filled('observacion_resultado')) {
            $instancia->observacion_resultado = $request->observacion_resultado;
        }
        if($request->filled('observacion_resultado_2')) {
            $instancia->observacion_resultado_2 = $request->observacion_resultado_2;
        }
        if($request->filled('observacion_resultado_3')) {
            $instancia->observacion_resultado_3 = $request->observacion_resultado_3;
        }
        if($request->filled('observacion_resultado_final')) {
            $instancia->observacion_resultado_final = $request->observacion_resultado_final;
        }

        $instancia->fecha_carga_ot = now();

        $instancia->save();
        DB::commit();

        // Verificar si es una petición AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Resultado del análisis actualizado correctamente',
                'data' => [
                    'resultado' => $instancia->resultado,
                    'resultado_2' => $instancia->resultado_2,
                    'resultado_3' => $instancia->resultado_3,
                    'resultado_final' => $instancia->resultado_final,
                    'observacion_resultado' => $instancia->observacion_resultado,
                    'observacion_resultado_2' => $instancia->observacion_resultado_2,
                    'observacion_resultado_3' => $instancia->observacion_resultado_3,
                    'observacion_resultado_final' => $instancia->observacion_resultado_final,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Resultado del análisis actualizado correctamente');

    } catch (\Exception $e) {
        DB::rollBack();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el análisis: ' . $e->getMessage()
            ], 500);
        }
        
        return redirect()->back()->with('error', 'Error al actualizar el análisis: ' . $e->getMessage());
    }
}



public function updateMediciones(Request $request, $instanciaId)
{
    try {
        $usuarioActual = trim(Auth::user()->usu_codigo);
        $instancia = CotioInstancia::where('id', $instanciaId)
            ->whereHas('responsablesMuestreo', function($query) use ($usuarioActual) {
                $query->where('usu.usu_codigo', $usuarioActual);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'valores' => 'required|array',
            'valores.*.valor' => 'nullable|string|max:255', // Allow empty or null values
            'valores.*.variable_id' => 'required|integer|exists:cotio_valores_variables,id,cotio_instancia_id,'.$instanciaId,
        ]);

        DB::beginTransaction();

        foreach ($request->valores as $variableId => $valorData) {
            $variable = CotioValorVariable::where('id', $valorData['variable_id'])
                ->where('cotio_instancia_id', $instanciaId)
                ->firstOrFail();

            $variable->update([
                'valor' => $valorData['valor'] ?? null // Store null if empty
            ]);
        }

        $instancia->update([
            'observaciones_medicion_muestreador' => trim($request->observaciones_medicion_muestreador),
        ]);

        DB::commit();

        return redirect()->back()->with('success', 'Variables actualizadas correctamente.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::debug('Validation errors', ['errors' => $e->errors()]);
        return redirect()->back()->withErrors($e->validator)->withInput();

    } catch (\Exception $e) {
        Log::error('Error updating mediciones', [
            'instancia_id' => $instanciaId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Error al actualizar las variables: ' . $e->getMessage());
    }
}


public function showTarea($cotio_numcoti, $cotio_item, $cotio_subitem) 
{
    $tarea = Cotio::with('vehiculo', 'cotizacion')
              ->where([
                  'cotio_numcoti' => $cotio_numcoti,
                  'cotio_item' => $cotio_item,
                  'cotio_subitem' => $cotio_subitem
              ])
              ->firstOrFail();

    $tarea->herramientas = InventarioLab::whereHas('cotioInventarioLab', function($q) use ($tarea) {
        $q->where([
            'cotio_numcoti' => $tarea->cotio_numcoti,
            'cotio_item' => $tarea->cotio_item,
            'cotio_subitem' => $tarea->cotio_subitem
        ]);
    })->get();

    return view('tareas.show', compact('tarea'));
}





public function showTareasAll($cotio_numcoti, $cotio_item, $cotio_subitem = 0, $instance = null)
{
    $instance = $instance ?? 1;
    $usuarioActual = trim(Auth::user()->usu_codigo);

    try {
        // Obtener la instancia de muestra principal con sus variables
        $instanciaMuestra = CotioInstancia::with([
            'muestra.vehiculo',
            'muestra.cotizacion',
            'herramientas',
            'valoresVariables' => function($query) {
                $query->select('id', 'cotio_instancia_id', 'variable', 'valor');
            }
        ])
        ->where('cotio_numcoti', $cotio_numcoti)
        ->where('cotio_item', $cotio_item)
        ->where('cotio_subitem', 0)
        ->where('instance_number', $instance)
        ->whereHas('responsablesMuestreo', function ($query) use ($usuarioActual) {
            $query->where('usu.usu_codigo', $usuarioActual);
        })
        ->firstOrFail();

        // Obtener análisis (subitems > 0) sin cargar las variables
        $analisis = CotioInstancia::with([
            'tarea.vehiculo',
            'tarea.cotizacion',
            'herramientas'
        ])
        ->where('cotio_numcoti', $cotio_numcoti)
        ->where('cotio_item', $cotio_item)
        ->where('cotio_subitem', '>', 0)
        ->where('instance_number', $instance)
        ->whereHas('responsablesMuestreo', function ($query) use ($usuarioActual) {
            $query->where('usu.usu_codigo', $usuarioActual);
        })
        ->orderBy('cotio_subitem')
        ->get();

        Log::debug('Tasks retrieved for muestreador', [
            'user' => $usuarioActual,
            'cotio_numcoti' => $cotio_numcoti,
            'cotio_item' => $cotio_item,
            'instance_number' => $instance,
            'sample_id' => $instanciaMuestra->id,
            'analysis_ids' => $analisis->pluck('id')->toArray(),
            'variables_count' => $instanciaMuestra->valoresVariables->count()
        ]);

        return view('tareas.show-by-categoria', [
            'instancia' => $instanciaMuestra,
            'analisis' => $analisis,
            'instanceNumber' => $instance,
            'variables' => $instanciaMuestra->valoresVariables // Pasamos solo las variables de la muestra principal
        ]);

    } catch (\Exception $e) {
        Log::error('Error retrieving tasks for muestreador', [
            'user' => $usuarioActual,
            'cotio_numcoti' => $cotio_numcoti,
            'cotio_item' => $cotio_item,
            'instance' => $instance,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(404, 'Muestra o tareas no encontradas o no asignadas al usuario.');
    }
}






public function generateAllQRs($cotizacion)
{
    try {
        $cotizacion = Coti::with('tareas')
            ->where('coti_num', $cotizacion)
            ->firstOrFail();
        
        $categorias = $cotizacion->tareas
            ->where('cotio_subitem', 0)
            ->reject(function ($tarea) {
                return $tarea->cotio_descripcion === 'TRABAJO TECNICO EN CAMPO';
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $categorias,
            'cotizacion' => [
                'numero' => $cotizacion->coti_num,
                'cliente' => $cotizacion->coti_empresa,
                'establecimiento' => $cotizacion->coti_establecimiento
            ]
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Cotización no encontrada'
        ], 404);
        
    } catch (\Exception $e) {
        Log::error('Error en generateAllQRs: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
}





public function enableOt(Request $request) 
{
    $request->validate([
        'cotio_numcoti' => 'required|string',
        'cotio_item' => 'required|string',
        'cotio_subitem' => 'required|string',
        'instance' => 'required|string',
    ]);

    try {
        DB::beginTransaction();
        
        // Buscar la instancia específica (usando first() ya que esperamos solo un registro)
        $instancia = DB::table('cotio_instancias')
            ->where('cotio_numcoti', $request->cotio_numcoti)
            ->where('cotio_item', $request->cotio_item)
            ->where('cotio_subitem', $request->cotio_subitem)
            ->where('instance_number', $request->instance)
            ->first();

        if (!$instancia) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la instancia especificada'
            ], 404);
        }

        // Actualizar el registro
        $result = DB::table('cotio_instancias')
            ->where('cotio_numcoti', $request->cotio_numcoti)
            ->where('cotio_item', $request->cotio_item)
            ->where('cotio_subitem', $request->cotio_subitem)
            ->where('instance_number', $request->instance)
            ->update([
                'enable_ot' => true,
                'complete_muestreo' => true,
                'cotio_estado_analisis' => null,
            ]);
        
        DB::commit();
        
        if ($result === 1) {
            return redirect()->back()->with('success', 'Instancia actualizada correctamente');

        } else {
            return redirect()->back()->with('error', 'No se pudo actualizar la instancia');
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar estados: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ], 500);
    }
}


public function disableOt(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required|string',
        'cotio_item' => 'required|string',
        'cotio_subitem' => 'required|string',
        'instance' => 'required|string',
    ]);

    try {
        DB::beginTransaction();
        
        $instancia = DB::table('cotio_instancias')
            ->where('cotio_numcoti', $request->cotio_numcoti)
            ->where('cotio_item', $request->cotio_item)
            ->where('cotio_subitem', $request->cotio_subitem)
            ->where('instance_number', $request->instance)
            ->first();

        if (!$instancia) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la instancia especificada'
            ], 404);
        }

        $result = DB::table('cotio_instancias')
            ->where('cotio_numcoti', $request->cotio_numcoti)
            ->where('cotio_item', $request->cotio_item)
            ->where('cotio_subitem', $request->cotio_subitem)
            ->where('instance_number', $request->instance)
            ->update([
                'enable_ot' => false,
                'complete_muestreo' => false,
                'cotio_estado_analisis' => null,
            ]);
        
        DB::commit();
        
        if ($result === 1) {
            return redirect()->back()->with('success', 'OT desactivada correctamente');

        } else {
            return redirect()->back()->with('error', 'No se pudo desactivar la OT');
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar estados: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ], 500);
    }
}



}
