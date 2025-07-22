<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Coti;
use App\Models\Cotio;
use App\Models\Matriz;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Provincia;
use App\Models\Localidad;
use App\Models\CotioInstancia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CotiController extends Controller
{
    public function index(Request $request)
    {
        $verTodas = $request->query('verTodas');
        $viewType = $request->get('view', 'lista');
        $matrices = Matriz::orderBy('matriz_descripcion')->get();
        $user = Auth::user();
        
        $cotizaciones = collect();
        $userToView = $request->get('user_to_view');
        $viewTasks = $request->get('view_tasks', false);
        $usuarios = collect();

        $currentMonth = $request->get('month') ? Carbon::parse($request->get('month')) : now();

        $provincias = Provincia::orderBy('nombre')->get();

        if ($request->has('provincia') && !empty($request->provincia)) {
            $localidades = Localidad::where('provincia_id', function($q) use ($request) {
                $q->select('id')->from('provincias')->where('codigo', $request->provincia)->limit(1);
            })->orderBy('nombre')->get();
        } else {
            $localidades = collect(); 
        }

        if ($user->usu_nivel >= 900 && $viewType === 'calendario') {
            $usuarios = User::where('usu_estado', true)
                ->orderBy('usu_descripcion')
                ->get(['usu_codigo', 'usu_descripcion']);
        }
    
        if ($viewType === 'calendario') {
            if ($user->usu_nivel >= 900 && $viewTasks && $userToView) {
                return $this->showUserTasksCalendar($request, $userToView);
            }
            
            $query = Coti::with('matriz');
            
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = '%'.$request->search.'%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('coti_num', 'like', $searchTerm)
                      ->orWhereRaw('LOWER(coti_empresa) LIKE ?', ['%'.strtolower($searchTerm).'%'])
                      ->orWhereRaw('LOWER(coti_establecimiento) LIKE ?', ['%'.strtolower($searchTerm).'%']);
                });
            }
            
            if ($request->has('matriz') && !empty($request->matriz)) {
                $query->whereHas('matriz', function($q) use ($request) {
                    $q->where('matriz_descripcion', 'like', '%'.$request->matriz.'%')
                      ->orWhere('matriz_codigo', $request->matriz);
                });
            }
            
            if ($request->has('estado') && !empty($request->estado)) {
                $query->where('coti_estado', $request->estado);
            } elseif (!$verTodas) {
                $query->where('coti_estado', 'A')->whereNotNull('coti_fechaaprobado');
            }
            
            if ($request->has('fecha_inicio') && !empty($request->fecha_inicio)) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('coti_fechaalta', '>=', $request->fecha_inicio)
                      ->orWhereDate('coti_fechafin', '>=', $request->fecha_inicio);
                });
            }
    
            if ($request->has('fecha_fin') && !empty($request->fecha_fin)) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('coti_fechaalta', '<=', $request->fecha_fin)
                      ->orWhereDate('coti_fechafin', '<=', $request->fecha_fin);
                });
            }

            if ($request->has('localidad') && !empty($request->localidad)) {
                $localidadBuscar = strtolower(str_replace(' ', '', $request->localidad));
                $query->whereRaw("REPLACE(LOWER(coti_localidad), ' ', '') = ?", [$localidadBuscar]);
            }
            
            
            $cotizaciones = $query->orderBy('coti_fechafin', 'asc')->get();
        
            $grouped = $cotizaciones->filter(fn($item) => !empty($item->coti_fechafin))
                ->groupBy(function($item) {
                    return \Carbon\Carbon::parse($item->coti_fechafin)->format('Y-m-d');
                });
            
            return view('cotizaciones.index', [
                'cotizaciones' => $grouped,
                'viewType' => $viewType,
                'request' => $request,
                'matrices' => $matrices,
                'userToView' => $userToView,
                'usuarios' => $usuarios,
                'viewTasks' => false,
                'currentMonth' => $currentMonth,
                'provincias' => $provincias,
                'localidades' => $localidades,
            ]);
        }
        
        $query = Coti::with(['matriz', 'responsable']);
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%'.$request->search.'%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('coti_num', 'like', $searchTerm)
                  ->orWhereRaw('LOWER(coti_empresa) LIKE ?', ['%'.strtolower($searchTerm).'%'])
                  ->orWhereRaw('LOWER(coti_establecimiento) LIKE ?', ['%'.strtolower($searchTerm).'%']);
            });
        }
        
        if ($request->has('matriz') && !empty($request->matriz)) {
            $query->whereHas('matriz', function($q) use ($request) {
                $q->where('matriz_descripcion', 'like', '%'.$request->matriz.'%')
                  ->orWhere('matriz_codigo', $request->matriz);
            });
        }
        
        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('coti_estado', $request->estado);
        } elseif (!$verTodas) {
            $query->where('coti_estado', 'A')->whereNotNull('coti_fechaaprobado');
        }
        
        if ($request->has('fecha_inicio') && !empty($request->fecha_inicio)) {
            $query->whereDate('coti_fechaalta', '>=', $request->fecha_inicio);
        }
    
        if ($request->has('fecha_fin') && !empty($request->fecha_fin)) {
            $query->whereDate('coti_fechaalta', '<=', $request->fecha_fin);
        }
        
        if ($request->has('provincia') && !empty($request->provincia)) {
            $provincia = Provincia::where('codigo', $request->provincia)->first();
            if ($provincia) {
                $query->where('coti_partido', 'like', '%' . $provincia->nombre . '%');
            }
        }
        
        if ($request->has('localidad') && !empty($request->localidad)) {
            $localidad = Localidad::where('codigo', $request->localidad)->first();
            if ($localidad) {
                $nombreLocalidad = strtolower(str_replace(' ', '', $localidad->nombre));
                $query->whereRaw("REPLACE(LOWER(coti_localidad), ' ', '') = ?", [$nombreLocalidad]);
            }
        }
        
        if (!empty($request->fecha_inicio) || !empty($request->fecha_fin)) {
            $query->orderBy('coti_fechaalta', 'desc');
        } else {
            $query->orderBy('coti_fechaaprobado', 'asc');
        }

        $cotizaciones = $query->paginate(20)->withQueryString();
        
        return view('cotizaciones.index', [
            'cotizaciones' => $cotizaciones,
            'viewType' => $viewType,
            'request' => $request,
            'matrices' => $matrices,
            'userToView' => $userToView,
            'usuarios' => $usuarios,
            'viewTasks' => false,
            'provincias' => $provincias,
            'localidades' => $localidades,
        ]);
    }




















    


    protected function showUserTasksCalendar(Request $request, $userCode)
    {
        $currentMonth = $request->get('month') ? Carbon::parse($request->get('month')) : now();
        
        $query = Cotio::with(['cotizacion', 'vehiculo'])
            ->where('cotio_subitem', '>', 0)
            // ->where('activo', true)
            ->where('cotio_responsable_codigo', trim($userCode)); 
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%'.$request->search.'%';
            $query->whereHas('cotizacion', function($q) use ($searchTerm) {
                $q->where('coti_num', 'LIKE', $searchTerm)
                  ->orWhere('coti_empresa', 'LIKE', $searchTerm)
                  ->orWhere('coti_establecimiento', 'LIKE', $searchTerm);
            });
        }
        
        $query->whereBetween('fecha_fin_muestreo', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        ]);
        
        $query->orderBy('fecha_fin_muestreo', 'asc');
        
        $tareas = $query->get();
        
        $cotizacionesIds = $tareas->pluck('cotio_numcoti')->unique();
        $cotizaciones = Coti::whereIn('coti_num', $cotizacionesIds)->get()->keyBy('coti_num');
        
        $tareasCalendario = $tareas->filter(fn($t) => !empty($t->fecha_fin_muestreo))
        ->mapToGroups(function($item) {
            return [Carbon::parse($item->fecha_fin_muestreo)->format('Y-m-d') => $item];
        })
        ->map(function($items) {
            return $items->sortBy('fecha_fin_muestreo');
        });


        return view('cotizaciones.partials.calendario', [
            'tareasCalendario' => $tareasCalendario,
            'cotizaciones' => collect(), 
            'viewType' => 'calendario',
            'request' => $request,
            'matrices' => Matriz::orderBy('matriz_descripcion')->get(),
            'userToView' => $userCode,
            'usuarios' => User::where('usu_estado', true)
                            ->orderBy('usu_descripcion')
                            ->get(['usu_codigo', 'usu_descripcion']),
            'viewTasks' => true,
            'currentMonth' => $currentMonth 
        ]);
    }


    
    

    
    public function showTareas(Request $request)
    {
        $user = Auth::user();
        $codigo = trim($user->usu_codigo);
        $viewType = $request->get('view', 'lista');
        $perPage = 50;
        $searchTerm = $request->get('search');
        $fechaInicio = $request->get('fecha_inicio_muestreo');
        $fechaFin = $request->get('fecha_fin_muestreo');
        $estado = $request->get('estado');
        
        $currentMonth = $request->get('month') 
            ? Carbon::parse($request->get('month')) 
            : now();
    
        // Initialize queries
        $queryMuestras = CotioInstancia::with([
            'muestra.cotizado',
            'muestra.vehiculo',
            'vehiculo',
            'herramientas',
            'responsablesMuestreo',
            'tareas.responsablesAnalisis'
        ])->where('cotio_subitem', 0)->where('cotio_estado', '!=', 'muestreado');
    
        $queryAnalisis = CotioInstancia::with([
            'tarea.cotizado',
            'tarea.vehiculo',
            'vehiculo',
            'herramientas',
            'responsablesAnalisis'
        ])->where('cotio_subitem', '>', 0)->where('cotio_estado', '!=', 'muestreado');
    
        // Modificación para ordenar muestras por fecha de coordinación
        $queryMuestras->where('active_ot', false)
                    ->where(function ($query) use ($codigo) {
                        $query->whereHas('responsablesMuestreo', function ($q) use ($codigo) {
                            $q->where('usu.usu_codigo', $codigo);
                        })->orWhereHas('tareas', function ($q) use ($codigo) {
                            $q->where('cotio_subitem', '>', 0)
                                ->where('active_ot', false)
                                ->where('active_muestreo', true)
                                ->whereHas('responsablesMuestreo', function ($subQ) use ($codigo) {
                                    $subQ->where('usu.usu_codigo', $codigo);
                                });
                        });
                    })
                    // Ordenar por fecha de inicio de muestreo (más recientes primero)
                    ->orderBy('fecha_inicio_muestreo', 'desc')
                    // Segundo criterio: estado "coordinado muestreo" primero
                    ->orderByRaw("CASE WHEN cotio_estado = 'coordinado muestreo' THEN 0 ELSE 1 END");
    
        $queryAnalisis->where('active_ot', false)
                    ->whereHas('responsablesMuestreo', function ($q) use ($codigo) {
                        $q->where('usu.usu_codigo', $codigo);
                    })
                    // Ordenar análisis por la misma fecha
                    ->orderBy('fecha_inicio_muestreo', 'desc');
        if ($user->rol !== 'muestreador') {
            return redirect()->route('login')->with('error', 'Acceso denegado. Solo los muestreadores pueden ver estas tareas.');
        }
    
        if ($searchTerm) {
            $searchTerms = array_filter(explode(' ', trim($searchTerm)));
            $searchClosure = function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $searchTerm = '%' . strtolower($term) . '%';
                    $q->where(function ($subQuery) use ($searchTerm) {
                        $subQuery->where('coti_num', 'LIKE', $searchTerm)
                                 ->orWhereRaw('LOWER(coti_empresa) LIKE ?', [$searchTerm])
                                 ->orWhereRaw('LOWER(coti_establecimiento) LIKE ?', [$searchTerm])
                                 ->orWhereRaw('LOWER(coti_descripcion) LIKE ?', [$searchTerm]);
                    });
                }
            };
    
            $queryAnalisis->whereHas('tarea.cotizado', $searchClosure);
            $queryMuestras->whereHas('muestra.cotizado', $searchClosure);
        }
    
        // Apply date filters
        if ($fechaInicio) {
            $queryAnalisis->whereDate('fecha_inicio_muestreo', '>=', $fechaInicio);
            $queryMuestras->whereDate('fecha_inicio_muestreo', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $queryAnalisis->whereDate('fecha_fin_muestreo', '<=', $fechaFin);
            $queryMuestras->whereDate('fecha_fin_muestreo', '<=', $fechaFin);
        }

        if ($estado) {
            $queryMuestras->where('cotio_estado', $estado);
        }


        // Fetch data
        $muestras = $queryMuestras->get()->unique(function ($item) {
            return $item->cotio_numcoti . '_' . $item->instance_number . '_' . $item->cotio_item;
        });
    
        $todosAnalisis = $queryAnalisis->get();
    
        // Group tasks by sample
        $tareasAgrupadas = collect();
        foreach ($muestras as $muestra) {
            $key = $muestra->cotio_numcoti . '_' . $muestra->instance_number . '_' . $muestra->cotio_item;
            $tareasAgrupadas->put($key, [
                'muestra' => $muestra->muestra,
                'instancia_muestra' => $muestra,
                'analisis' => collect(),
                'cotizado' => $muestra->muestra->cotizado ?? null,
                'vehiculo' => $muestra->vehiculo ?? null,
                'responsables_muestreo' => $muestra->responsablesMuestreo
            ]);
        }
    
        // Assign analyses to their respective samples
        foreach ($todosAnalisis as $analisis) {
            $key = $analisis->cotio_numcoti . '_' . $analisis->instance_number . '_' . $analisis->cotio_item;
            if ($tareasAgrupadas->has($key)) {
                $tareasAgrupadas[$key]['analisis']->push($analisis);
                $tareasAgrupadas[$key]['analisis']->last()->responsables_analisis = $analisis->responsablesAnalisis;
            } else {
                $relatedSample = CotioInstancia::where([
                    'cotio_numcoti' => $analisis->cotio_numcoti,
                    'cotio_item' => $analisis->cotio_item,
                    'instance_number' => $analisis->instance_number,
                    'cotio_subitem' => 0
                ])->first();
    
                if ($relatedSample) {
                    $tareasAgrupadas->put($key, [
                        'muestra' => $relatedSample->muestra,
                        'instancia_muestra' => $relatedSample,
                        'analisis' => collect([$analisis]),
                        'cotizado' => $relatedSample->muestra->cotizado ?? null,
                        'vehiculo' => $relatedSample->vehiculo ?? null,
                        'responsables_muestreo' => $relatedSample->responsablesMuestreo
                    ]);
                    $tareasAgrupadas[$key]['analisis']->last()->responsables_analisis = $analisis->responsablesAnalisis;
                    Log::debug('Sample added for analysis', [
                        'key' => $key,
                        'analysis_id' => $analisis->id,
                        'sample_id' => $relatedSample->id
                    ]);
                }
            }
        }
    
        // Prepare pagination for all tasks (samples + analyses)
        $allTasks = $muestras->merge($todosAnalisis)->values();
        $tareasPaginadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTasks->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $perPage),
            $allTasks->count(),
            $perPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    

        // Fetch related quotations
        $cotizacionesIds = $todosAnalisis->pluck('cotio_numcoti')
            ->merge($muestras->pluck('cotio_numcoti'))
            ->unique();
        $cotizaciones = Coti::whereIn('coti_num', $cotizacionesIds)->get()->keyBy('coti_num');

        $muestrasCalendario = [];

        if ($viewType === 'calendario') {
            $events = $muestras->map(function ($muestra) use ($user) {
                $descripcion = $muestra->cotio_descripcion ?? ($muestra->muestra->cotio_descripcion ?? 'Muestra sin descripción');
                $empresa = $muestra->muestra && $muestra->muestra->cotizacion 
                    ? $muestra->muestra->cotizacion->coti_empresa 
                    : '';
                
                $estado = strtolower($muestra->cotio_estado ?? 'pendiente');
                $className = match ($estado) {
                    'pendiente', 'coordinado muestreo' => 'fc-event-warning',
                    'en proceso', 'en revision muestreo' => 'fc-event-info',
                    'muestreado' => 'fc-event-success',
                    'suspension' => 'fc-event-danger',
                    default => 'fc-event-primary'
                };
    
                return [
                    'id' => $muestra->id,
                    'title' => Str::limit($descripcion, 30),
                    'start' => $muestra->fecha_inicio_muestreo,
                    'end' => $muestra->fecha_fin_muestreo,
                    'className' => $className,
                    'url' => route('tareas.all.show', [
                        $muestra->cotio_numcoti ?? 'N/A', 
                        $muestra->cotio_item ?? 'N/A', 
                        $muestra->cotio_subitem ?? 'N/A', 
                        $muestra->instance_number ?? 'N/A'
                    ]),
                    'extendedProps' => [
                        'descripcion' => $descripcion,
                        'empresa' => $empresa,
                        'estado' => $estado,
                        'responsables' => $muestra->responsablesMuestreo->pluck('usu_nombre')->implode(', '),
                        'analisis_count' => $muestra->tareas->count()
                    ]
                ];
            });
    
            return view('tareas.index', [
                'tareasAgrupadas' => $tareasAgrupadas,
                'cotizaciones' => $cotizaciones,
                'tareasPaginadas' => $tareasPaginadas,
                'events' => $events,
                'viewType' => $viewType,
                'request' => $request,
                'currentMonth' => $currentMonth
            ]);
        }
    

    
        return view('tareas.index', [
            'tareasAgrupadas' => $tareasAgrupadas,
            'cotizaciones' => $cotizaciones,
            'tareasPaginadas' => $tareasPaginadas,
            'tareasCalendario' => $muestrasCalendario,
            'muestras' => $muestras,
            'viewType' => $viewType,
            'request' => $request,
            'currentMonth' => $currentMonth
        ]);
    }



    
    public function generateFullPdf($cotizacion)
    {
        $cotizacion = Coti::with(['tareas' => function($query) {
            $query->orderBy('cotio_item')
                  ->orderBy('cotio_subitem');
        }])->findOrFail($cotizacion);
    
        $agrupadas = [];
        $categoriaActual = null;
    
        foreach ($cotizacion->tareas as $tarea) {
            if ($tarea->cotio_subitem == 0) {
                $categoriaActual = $tarea;
                $agrupadas[] = [
                    'categoria' => $tarea,
                    'tareas' => []
                ];
            } else {
                if ($categoriaActual) {
                    $index = count($agrupadas) - 1;
                    $agrupadas[$index]['tareas'][] = $tarea;
                }
            }
        }
    
        $agrupadas = array_filter($agrupadas, function($grupo) {
            return collect($grupo['tareas'])->contains(function($tarea) {
                return $tarea->activo;
            });
        });
    
        $data = [
            'cotizacion' => $cotizacion,
            'agrupadas' => $agrupadas
        ];
    
        $pdf = Pdf::loadView('pdf.cotizacion-completa', $data);
        return $pdf->stream("cotizacion-{$cotizacion->coti_num}-completa.pdf");
    }
    


    public function printAllQr($coti_num)
    {
        $cotizacion = Coti::findOrFail($coti_num);
        
        $instancias = CotioInstancia::with(['muestra'])  
                      ->where('cotio_numcoti', $coti_num)
                      ->where('active_muestreo', true)
                      ->where('cotio_subitem', 0)
                      ->orderBy('cotio_item')
                      ->orderBy('instance_number')
                      ->get();
    
        return view('cotizaciones.print-all-qr', [
            'cotizacion' => $cotizacion,
            'instancias' => $instancias
        ]);
    }



    public function showDetalle($cotizacion) {
        // Obtener la cotización con sus tareas ordenadas
        $cotizacion = Coti::with(['tareas' => function($query) {
            $query->orderBy('cotio_item')
                  ->orderBy('cotio_subitem');
        }])->findOrFail($cotizacion);
    
        // Obtener todas las tareas de la cotización
        $tareas = $cotizacion->tareas;
    
        // Cargar instancias existentes con sus relaciones
        $instanciasExistentes = CotioInstancia::where('cotio_numcoti', $cotizacion->coti_num)
                                ->with(['responsablesMuestreo', 'muestra'])
                                ->get()
                                ->groupBy(['cotio_item', 'cotio_subitem', 'instance_number']);
    
        $agrupadas = [];
        
        // Descripciones a excluir
        $excludedDescriptions = [
            'TRABAJO TECNICO EN CAMPO',
            'TRABAJOS EN CAMPO NOCTURNO - VIATICOS',
            'VIATICOS'
        ];
    
        foreach ($tareas as $tarea) {
            if ($tarea->cotio_subitem == 0) { // Es una muestra
                // Verificar si la descripción está en la lista de exclusiones
                // if (in_array($tarea->cotio_descripcion, $excludedDescriptions)) {
                //     continue; // Saltar esta iteración
                // }
                
                $cantidad = $tarea->cotio_cantidad ?: 1;
    
                for ($i = 1; $i <= $cantidad; $i++) {
                    $instancia = $this->getOrCreateInstancia(
                        $tarea->cotio_numcoti,
                        $tarea->cotio_item,
                        0, // subitem 0 para muestras
                        $i,
                        $instanciasExistentes
                    );
    
                    $agrupadas[] = [
                        'muestra' => (object) array_merge($tarea->toArray(), [
                            'instance_number' => $i,
                            'original_item' => $tarea->cotio_item,
                            'display_item' => $tarea->cotio_item . '-' . $i
                        ]),
                        'instancia' => $instancia,
                        'analisis' => $this->getAnalisisForMuestra($tareas, $tarea->cotio_item, $i, $instanciasExistentes),
                        'responsables' => $instancia->responsablesMuestreo
                    ];
                }
            }
        }
    
        return view('cotizaciones.showDetalle', compact('cotizacion', 'agrupadas'));
    }

    // Método auxiliar para obtener o crear instancia (similar al del método show)
    protected function getOrCreateInstancia($numcoti, $item, $subitem, $instanceNumber, $instanciasExistentes)
    {
        if (isset($instanciasExistentes[$item][$subitem][$instanceNumber])) {
            return $instanciasExistentes[$item][$subitem][$instanceNumber]->first();
        }
    
        return new CotioInstancia([
            'cotio_numcoti' => $numcoti,
            'cotio_item' => $item,
            'cotio_subitem' => $subitem,
            'instance_number' => $instanceNumber,
            'active_muestreo' => true
        ]);
    }
    
    // Método auxiliar para obtener análisis asociados a una muestra (similar al del método show)
    protected function getAnalisisForMuestra($tareas, $item, $instanceNumber, $instanciasExistentes)
    {
        $analisis = [];
        
        foreach ($tareas as $tarea) {
            if ($tarea->cotio_subitem > 0 && $tarea->cotio_item == $item) {
                $instancia = $this->getOrCreateInstancia(
                    $tarea->cotio_numcoti,
                    $tarea->cotio_item,
                    $tarea->cotio_subitem,
                    $instanceNumber,
                    $instanciasExistentes
                );
    
                $analisis[] = [
                    'tarea' => $tarea,
                    'instancia' => $instancia
                ];
            }
        }
    
        return $analisis;
    }







}
