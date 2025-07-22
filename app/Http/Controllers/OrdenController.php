<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coti;
use App\Models\Matriz;
use Illuminate\Support\Facades\DB;
use App\Models\Cotio;
use App\Models\User;
use App\Models\InventarioLab;
use App\Models\Vehiculo;
use App\Models\CotioInstancia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\InstanciaResponsableAnalisis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrdenController extends Controller
{



    public function index(Request $request)
    {
        $verTodas = $request->query('ver_todas');
        $viewType = $request->get('view', 'lista');
        $matrices = Matriz::orderBy('matriz_descripcion')->get();
        $user = Auth::user();
        $currentMonth = $request->get('month') ? Carbon::parse($request->get('month')) : now();
        $startOfWeek = $request->get('week') ? Carbon::parse($request->get('week'))->startOfWeek() : now()->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();
    
        // Consulta base para todas las vistas
        $baseQuery = CotioInstancia::with([
            'cotizacion.matriz',
            'tarea',
            'responsablesAnalisis',
            'cotizacion.instancias' => function ($q) {
                $q->select('id', 'cotio_numcoti', 'cotio_estado_analisis');
            }
        ])->where('enable_ot', true);
    
        if ($viewType === 'calendario') {
            $query = clone $baseQuery;
    
            // Aplicar filtros
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = '%'.$request->search.'%';
                $query->whereHas('cotizacion', function($q) use ($searchTerm) {
                    $q->where('coti_num', 'like', $searchTerm)
                        ->orWhereRaw('LOWER(coti_empresa) LIKE ?', [strtolower($searchTerm)])
                        ->orWhereRaw('LOWER(coti_establecimiento) LIKE ?', [strtolower($searchTerm)]);
                });
            }
    
            if ($request->has('matriz') && !empty($request->matriz)) {
                $query->whereHas('cotizacion', function($q) use ($request) {
                    $q->where('coti_matriz', $request->matriz);
                });
            }
    
            if ($request->has('estado') && !empty($request->estado)) {
                $query->where('cotio_estado_analisis', $request->estado);
            } elseif (!$verTodas) {
                $query->whereHas('cotizacion', function($q) {
                    $q->where('coti_estado', 'A');
                });
            }
    
            // Filtros por rango de fechas
            if ($request->has('fecha_inicio_ot') && !empty($request->fecha_inicio_ot)) {
                $query->whereDate('fecha_inicio_ot', '>=', $request->fecha_inicio_ot);
            }
    
            if ($request->has('fecha_fin_ot') && !empty($request->fecha_fin_ot)) {
                $query->whereDate('fecha_fin_ot', '<=', $request->fecha_fin_ot);
            } else {
                // Mostrar por defecto el mes actual si no hay fecha_fin
                $query->whereBetween('fecha_inicio_ot', [
                    $currentMonth->copy()->startOfMonth(),
                    $currentMonth->copy()->endOfMonth()
                ]);
            }
    
            // Obtener resultados
            $instancias = $query->orderBy('fecha_inicio_ot', 'asc')->get();
    
            // Verificar suspensiones
            $instancias->each(function ($instancia) {
                $instancia->has_suspension = $instancia->cotizacion->instancias->contains(function ($i) {
                    return strtolower(trim($i->cotio_estado_analisis)) === 'suspension';
                });
            });
    
            // Agrupar por fecha de inicio
            $tareasCalendario = $instancias
                ->filter(fn($item) => !empty($item->fecha_inicio_ot))
                ->mapToGroups(function($instancia) {
                    return [Carbon::parse($instancia->fecha_inicio_ot)->format('Y-m-d') => $instancia];
                })
                ->map(function($items) {
                    return $items->sortBy('fecha_inicio_ot');
                });
    
            // Instancias sin fecha programada
            $unscheduled = $instancias->filter(fn($instancia) => empty($instancia->fecha_inicio_ot));
            if ($unscheduled->isNotEmpty()) {
                $tareasCalendario->put('sin-fecha', $unscheduled);
            }
    
            // Generar eventos para FullCalendar
            $events = collect();
            foreach ($tareasCalendario as $date => $instancias) {
                foreach ($instancias as $instancia) {
                    $events->push([
                        'title' => $instancia->cotizacion->coti_empresa . ' - ' . $instancia->cotio_numcoti,
                        'start' => $instancia->fecha_inicio_ot,
                        'end' => $instancia->fecha_fin_ot ?? null,
                        'url' => route('categoria.verOrden', [
                            'cotizacion' => $instancia->cotio_numcoti,
                            'item' => $instancia->cotio_item,
                            'cotio_subitem' => $instancia->cotio_subitem,
                            'instance' => $instancia->instance_number
                        ]),
                        'extendedProps' => [
                            'empresa' => $instancia->cotizacion->coti_empresa,
                            'descripcion' => $instancia->cotizacion->coti_descripcion ?? '',
                            'estado' => $instancia->cotio_estado_analisis,
                            'analisis_count' => $instancia->responsablesAnalisis->count() ?? 0,
                            'has_suspension' => $instancia->has_suspension
                        ],
                        'className' => $this->getEventClass($instancia),
                    ]);
                }
            }
    
            return view('ordenes.index', [
                'events' => $events,
                'tareasCalendario' => $tareasCalendario,
                'startOfWeek' => $startOfWeek,
                'endOfWeek' => $endOfWeek,
                'viewType' => $viewType,
                'matrices' => $matrices,
                'request' => $request,
                'currentMonth' => $currentMonth,
                'userToView' => null,
                'usuarios' => collect(),
                'viewTasks' => false
            ]);
        }
    
        $query = clone $baseQuery;
    
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%'.$request->search.'%';
            $query->whereHas('cotizacion', function($q) use ($searchTerm) {
                $q->where('coti_num', 'like', $searchTerm)
                    ->orWhereRaw('LOWER(coti_empresa) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(coti_establecimiento) LIKE ?', [strtolower($searchTerm)]);
            });
        }
    
        if ($request->has('matriz') && !empty($request->matriz)) {
            $query->whereHas('cotizacion', function($q) use ($request) {
                $q->where('coti_matriz', $request->matriz);
            });
        }
    
        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('cotio_estado_analisis', $request->estado);
        } elseif (!$verTodas) {
            $query->whereHas('cotizacion', function($q) {
                $q->where('coti_estado', 'A');
            });
        }
    
        $pagination = $query->orderBy('cotio_numcoti', 'desc')
            ->orderBy('cotio_item', 'asc')
            ->orderBy('cotio_subitem', 'asc')
            ->orderBy('instance_number', 'asc')
            ->paginate($viewType === 'documento' ? 20 : 10);
    
        $ordenes = $pagination->groupBy('cotio_numcoti')->map(function ($group) {
            $cotizacion = $group->first()->cotizacion;
            $total = $group->count();
            $completadas = $group->where('cotio_estado_analisis', 'analizado')->count();
            $enProceso = $group->where('cotio_estado_analisis', 'en revision analisis')->count();
            $coordinadas = $group->where('cotio_estado_analisis', 'coordinado analisis')->count();
            $suspendidas = $group->where('cotio_estado_analisis', 'suspension')->count();
            $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
    
            return [
                'instancias' => $group,
                'cotizacion' => $cotizacion,
                'total' => $total,
                'completadas' => $completadas,
                'en_proceso' => $enProceso,
                'coordinadas' => $coordinadas,
                'suspendidas' => $suspendidas,
                'porcentaje' => $porcentaje,
                'has_suspension' => $group->contains(function ($instancia) {
                    return strtolower(trim($instancia->cotio_estado_analisis)) === 'suspension';
                })
            ];
        });
    
        return view('ordenes.index', [
            'ordenes' => $ordenes,
            'viewType' => $viewType,
            'matrices' => $matrices,
            'pagination' => $pagination,
            'request' => $request,
            'currentMonth' => $currentMonth
        ]);
    }
    
    protected function getEventClass($instancia)
    {
        switch (strtolower($instancia->cotio_estado_analisis)) {
            case 'coordinado analisis':
                return 'fc-event-warning';
            case 'en revision analisis':
                return 'fc-event-info';
            case 'analizado':
                return 'fc-event-success';
            case 'suspension':
                return 'fc-event-danger';
            default:
                return 'fc-event-primary';
        }
    }





public function showOrdenes(Request $request)
{
    $user = Auth::user();
    $codigo = trim($user->usu_codigo);
    $viewType = $request->get('view', 'lista');
    $perPage = 50;
    $searchTerm = $request->get('search');
    $fechaInicio = $request->get('fecha_inicio_ot');
    $fechaFin = $request->get('fecha_fin_ot');
    $estado = $request->get('estado');
    
    $currentMonth = $request->get('month') 
        ? Carbon::parse($request->get('month')) 
        : now();

    // Initialize queries with ordering
    $queryMuestras = CotioInstancia::with([
        'muestra.cotizado',
        'muestra.vehiculo',
        'vehiculo',
        'herramientas',
        'responsablesAnalisis',
        'tareas.responsablesAnalisis'
    ])
    ->where('cotio_subitem', 0)
    // Ordenar por fecha de inicio OT (más recientes primero)
    ->orderBy('fecha_inicio_ot', 'desc')
    // Segundo criterio: estado "coordinado" primero
    ->orderByRaw("CASE WHEN cotio_estado_analisis = 'coordinado' THEN 0 ELSE 1 END");

    $queryAnalisis = CotioInstancia::with([
        'tarea.cotizado',
        'tarea.vehiculo',
        'vehiculo',
        'herramientas',
        'responsablesAnalisis'
    ])
    ->where('cotio_subitem', '>', 0)
    // Mismo orden para análisis
    ->orderBy('fecha_inicio_ot', 'desc')
    ->orderByRaw("CASE WHEN cotio_estado_analisis = 'coordinado' THEN 0 ELSE 1 END");

    // Conditions for queries
    $queryMuestras->where('active_ot', true)
    ->where(function ($query) use ($codigo) {
        $query->whereHas('responsablesAnalisis', function ($q) use ($codigo) {
            $q->where('usu.usu_codigo', $codigo);
        })->orWhereHas('tareas', function ($q) use ($codigo) {
            $q->where('cotio_subitem', '>', 0)
                ->where('active_ot', true)
                ->whereHas('responsablesAnalisis', function ($subQ) use ($codigo) {
                    $subQ->where('usu.usu_codigo', $codigo);
                });
        });
    });

    $queryAnalisis->where('active_ot', true)
            ->whereHas('responsablesAnalisis', function ($q) use ($codigo) {
                $q->where('usu.usu_codigo', $codigo);
            });

    // Apply search filter
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
        $queryAnalisis->whereDate('fecha_inicio_ot', '>=', $fechaInicio);
        $queryMuestras->whereDate('fecha_inicio_ot', '>=', $fechaInicio);
    }
    if ($fechaFin) {
        $queryAnalisis->whereDate('fecha_fin_ot', '<=', $fechaFin);
        $queryMuestras->whereDate('fecha_fin_ot', '<=', $fechaFin);
    }

    if ($estado) {
        $queryMuestras->where('cotio_estado_analisis', $estado);
    }


    // Fetch data with ordering
    $muestras = $queryMuestras->get()->unique(function ($item) {
        return $item->cotio_numcoti . '_' . $item->instance_number . '_' . $item->cotio_item;
    });

    $todosAnalisis = $queryAnalisis->get();

    // Group tasks by sample (manteniendo el orden)
    $ordenesAgrupadas = collect();
    foreach ($muestras as $muestra) {
        $key = $muestra->cotio_numcoti . '_' . $muestra->instance_number . '_' . $muestra->cotio_item;
        $ordenesAgrupadas->put($key, [
            'muestra' => $muestra->muestra,
            'instancia_muestra' => $muestra,
            'analisis' => collect(),
            'cotizado' => $muestra->muestra->cotizado ?? null,
            'vehiculo' => $muestra->vehiculo ?? null,
            'responsables_muestreo' => $muestra->responsablesAnalisis
        ]);
    }

    // Assign analyses to their respective samples
    foreach ($todosAnalisis as $analisis) {
        $key = $analisis->cotio_numcoti . '_' . $analisis->instance_number . '_' . $analisis->cotio_item;
        if ($ordenesAgrupadas->has($key)) {
            $ordenesAgrupadas[$key]['analisis']->push($analisis);
            $ordenesAgrupadas[$key]['analisis']->last()->responsables_analisis = $analisis->responsablesAnalisis;
        } else {
            $instancia = CotioInstancia::where([
                'cotio_numcoti' => $analisis->cotio_numcoti,
                'cotio_item' => $analisis->cotio_item,
                'instance_number' => $analisis->instance_number,
                'cotio_subitem' => $analisis->cotio_subitem
            ])->first();
            $gemelos = $instancia ? $instancia->gemelos() : collect();
            $relatedSample = CotioInstancia::where([
                'cotio_numcoti' => $analisis->cotio_numcoti,
                'cotio_item' => $analisis->cotio_item,
                'instance_number' => $analisis->instance_number,
                'cotio_subitem' => 0
            ])->first();

            if ($relatedSample) {
                $ordenesAgrupadas->put($key, [
                    'muestra' => $relatedSample->muestra,
                    'instancia_muestra' => $relatedSample,
                    'analisis' => collect([$analisis]),
                    'cotizado' => $relatedSample->muestra->cotizado ?? null,
                    'vehiculo' => $relatedSample->vehiculo ?? null,
                    'responsables_muestreo' => $relatedSample->responsablesAnalisis
                ]);
                $ordenesAgrupadas[$key]['analisis']->last()->responsables_analisis = $analisis->responsablesAnalisis;
            }
        }
    }

    // Prepare pagination for analyses (manteniendo orden)
    $tareasPaginadas = new \Illuminate\Pagination\LengthAwarePaginator(
        $todosAnalisis->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $perPage),
        $todosAnalisis->count(),
        $perPage,
        \Illuminate\Pagination\Paginator::resolveCurrentPage(),
        ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
    );

    // Prepare calendar data (ya ordenado por fecha)
    $muestrasCalendario = $muestras->groupBy(function ($muestra) {
        return $muestra->fecha_inicio_ot 
            ? Carbon::parse($muestra->fecha_inicio_ot)->format('Y-m-d')
            : 'sin-fecha';
    })->map(function ($dayMuestras) {
        return $dayMuestras->sortByDesc('fecha_inicio_ot');
    });

    // Prepare events for calendar view
    $events = collect();
    if ($viewType === 'calendario') {
        $events = $muestras->map(function ($muestra) use ($user) {
            $descripcion = $muestra->cotio_descripcion ?? ($muestra->muestra->cotio_descripcion ?? 'Muestra sin descripción');
            $empresa = $muestra->muestra && $muestra->muestra->cotizacion 
                ? $muestra->muestra->cotizacion->coti_empresa 
                : '';
            
            $estado = strtolower($muestra->cotio_estado_analisis ?? 'coordinado');
            $className = match ($estado) {
                'coordinado', 'coordinado muestreo', 'coordinado analisis' => 'fc-event-warning',
                'en proceso', 'en revision muestreo', 'en revision analisis' => 'fc-event-info',
                'finalizado', 'muestreado', 'analizado' => 'fc-event-success',
                'suspension' => 'fc-event-danger',
                default => 'fc-event-primary'
            };
        
            return [
                'id' => $muestra->id,
                'title' => Str::limit($descripcion, 30),
                'start' => $muestra->fecha_inicio_ot,
                'end' => $muestra->fecha_fin_ot,
                'className' => $className,
                'url' => $user->rol == 'laboratorio' 
                    ? route('ordenes.all.show', [
                        $muestra->cotio_numcoti ?? 'N/A', 
                        $muestra->cotio_item ?? 'N/A', 
                        $muestra->cotio_subitem ?? 'N/A', 
                        $muestra->instance_number ?? 'N/A'
                    ])
                    : route('tareas.all.show', [
                        $muestra->cotio_numcoti ?? 'N/A', 
                        $muestra->cotio_item ?? 'N/A', 
                        $muestra->cotio_subitem ?? 'N/A', 
                        $muestra->instance_number ?? 'N/A'
                    ]),
                'extendedProps' => [
                    'descripcion' => $descripcion,
                    'empresa' => $empresa,
                    'estado' => $estado,
                    'tipo' => 'muestra',
                ]
            ];
        });
    }

    // SUGERENCIAS DE ANALITOS POR ESTADO
    $analitosSugeridos = collect();
    if ($estado) {
        $analitosSugeridos = $todosAnalisis->filter(function($analisis) use ($estado) {
            return $analisis->cotio_estado_analisis === $estado;
        });
    }

    // Fetch related quotations
    $cotizacionesIds = $todosAnalisis->pluck('cotio_numcoti')
        ->merge($muestras->pluck('cotio_numcoti'))
        ->unique();
    $cotizaciones = Coti::whereIn('coti_num', $cotizacionesIds)->get()->keyBy('coti_num');

    return view('mis-ordenes.index', [
        'ordenesAgrupadas' => $ordenesAgrupadas,
        'cotizaciones' => $cotizaciones,
        'tareasPaginadas' => $tareasPaginadas,
        'tareasCalendario' => $muestrasCalendario,
        'muestras' => $muestras,
        'viewType' => $viewType,
        'request' => $request,
        'currentMonth' => $currentMonth,
        'events' => $events,
        'analitosSugeridos' => $analitosSugeridos,
    ]);
}


public function showDetalle($ordenId)
{
    $cotizacion = Coti::findOrFail($ordenId);
    //solo usuario lab y lab1
    $usuarios = User::whereIn('usu_codigo', ['LAB1', 'LAB'])->get();
    $inventario = InventarioLab::all();

    $categoriasHabilitadas = $cotizacion->tareas()
        ->where('cotio_subitem', 0)
        ->orderBy('cotio_item')
        ->get();

    $categoriasIds = $categoriasHabilitadas->pluck('cotio_item')->toArray();

    $tareas = $cotizacion->tareas()
        ->whereIn('cotio_item', $categoriasIds)
        ->where('cotio_subitem', '!=', 0)
        ->orderBy('cotio_item')
        ->orderBy('cotio_subitem')
        ->get();

        $usuarios = User::withCount(['tareas' => function($query) use ($ordenId) {
            $query->where('cotio_numcoti', $ordenId);
            
            // Verificar si la columna existe antes de usarla
            if (Schema::hasColumn('cotio', 'cotio_estado_analisis')) {
                $query->where('cotio_estado_analisis', '!=', 'analizado');
            } else {
                $query->where('cotio_estado', '!=', 'analizado');
            }
        }])
        ->whereIn('usu_codigo', ['LAB1', 'LAB'])
        ->orderBy('usu_descripcion')
        ->get();

    $agrupadas = [];

    foreach ($categoriasHabilitadas as $categoria) {
        $item = $categoria->cotio_item;

        $instanciasMuestra = CotioInstancia::with('herramientas', 'responsablesAnalisis')
            ->where([
                'cotio_numcoti' => $cotizacion->coti_num,
                'cotio_item' => $item,
                'cotio_subitem' => 0,
                'enable_ot' => true
            ])
            ->orderBy('instance_number')
            ->get();

        $tareasDeCategoria = $tareas->where('cotio_item', $item);

        $instanciasConAnalisis = $instanciasMuestra->map(function($instanciaMuestra) use ($tareasDeCategoria, $cotizacion) {
            $analisisParaInstancia = $tareasDeCategoria->map(function($tarea) use ($instanciaMuestra, $cotizacion) {
                $tareaClonada = clone $tarea;
                
                $instanciaAnalisis = CotioInstancia::with('herramientas', 'responsablesAnalisis')
                    ->where([
                        'cotio_numcoti' => $cotizacion->coti_num,
                        'cotio_item' => $tarea->cotio_item,
                        'cotio_subitem' => $tarea->cotio_subitem,
                        'instance_number' => $instanciaMuestra->instance_number
                    ])
                    ->first();

                if ($instanciaAnalisis) {
                    $tareaClonada->instancia = $instanciaAnalisis;
                }

                return $tareaClonada;
            });

            return [
                'muestra' => $instanciaMuestra,
                'analisis' => $analisisParaInstancia
            ];
        });

        $agrupadas[] = [
            'categoria' => $categoria,
            'instancias' => $instanciasConAnalisis
        ];
    }

    return view('ordenes.show', compact('cotizacion', 'usuarios', 'agrupadas', 'inventario'));
}




public function verOrden($cotizacion, $item, $instance = null)
{
    $cotizacion = Coti::findOrFail($cotizacion);
    $instance = $instance ?? 1;

    // Obtener la muestra principal
    $categoria = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
                ->where('cotio_item', $item)
                ->where('cotio_subitem', 0)
                ->firstOrFail();

    // Obtener la instancia de la muestra con responsables de análisis
    $instanciaMuestra = CotioInstancia::with(['responsablesAnalisis', 'valoresVariables'])
                ->where([
                    'cotio_numcoti' => $cotizacion->coti_num,
                    'cotio_item' => $item,
                    'cotio_subitem' => 0,
                    'instance_number' => $instance,
                ])->first();

    $variablesOrdenadas = collect();
    if ($instanciaMuestra->valoresVariables) {
        $variablesOrdenadas = $instanciaMuestra->valoresVariables
            ->sortBy('variable')
            ->values();
    }

    // Obtener herramientas manualmente para la instancia de muestra
    if ($instanciaMuestra) {
        $herramientasMuestra = DB::table('cotio_inventario_muestreo')
            ->where('cotio_numcoti', $instanciaMuestra->cotio_numcoti)
            ->where('cotio_item', $instanciaMuestra->cotio_item)
            ->where('cotio_subitem', $instanciaMuestra->cotio_subitem)
            ->where('instance_number', $instanciaMuestra->instance_number)
            ->join('inventario_muestreo', 'cotio_inventario_muestreo.inventario_muestreo_id', '=', 'inventario_muestreo.id')
            ->select(
                'inventario_muestreo.*',
                'cotio_inventario_muestreo.cantidad',
                'cotio_inventario_muestreo.observaciones as pivot_observaciones'
            )
            ->get();

        $instanciaMuestra->herramientas = $herramientasMuestra;
    }

    if (!$instanciaMuestra) {
        return view('ordenes.tareasporcategoria', [
            'cotizacion' => $cotizacion,
            'categoria' => $categoria,
            'tareas' => collect(),
            'usuarios' => collect(),
            'inventario' => collect(),
            'instance' => $instance,
            'instanciaActual' => null,
            'variablesMuestra' => $variablesOrdenadas,
            
            'instanciasMuestra' => collect()
        ]);
    }

    // Obtener tareas (análisis)
    $tareas = Cotio::where('cotio_numcoti', $cotizacion->coti_num)
                ->where('cotio_item', $item)
                ->where('cotio_subitem', '!=', 0)
                ->orderBy('cotio_subitem')
                ->get();

    $tareasConInstancias = $tareas->map(function($tarea) use ($instance) {
        $instancia = CotioInstancia::with(['responsablesAnalisis', 'valoresVariables'])
            ->where([
                'cotio_numcoti' => $tarea->cotio_numcoti,
                'cotio_item' => $tarea->cotio_item,
                'cotio_subitem' => $tarea->cotio_subitem,
                'instance_number' => $instance,
                'active_ot' => true
            ])->first();


        if ($instancia) {
            // Obtener herramientas manualmente para cada análisis
            $herramientasAnalisis = DB::table('cotio_inventario_lab')
                ->where('cotio_numcoti', $instancia->cotio_numcoti)
                ->where('cotio_item', $instancia->cotio_item)
                ->where('cotio_subitem', $instancia->cotio_subitem)
                ->where('instance_number', $instancia->instance_number)
                ->join('inventario_lab', 'cotio_inventario_lab.inventario_lab_id', '=', 'inventario_lab.id')
                ->select(
                    'inventario_lab.*',
                    'cotio_inventario_lab.cantidad',
                    'cotio_inventario_lab.observaciones as pivot_observaciones'
                )
                ->get();

            $instancia->herramientas = $herramientasAnalisis;
            $tarea->instancia = $instancia;
            return $tarea;
        }
        return null;
    })->filter();

    $usuarios = User::where('usu_nivel', '<=', 500)
                ->orderBy('usu_descripcion')
                ->get();

    $inventario = InventarioLab::all();
    $vehiculos = Vehiculo::all();

    // Obtener todas las instancias de muestra con responsables de análisis
    $instanciasMuestra = CotioInstancia::with(['responsablesAnalisis', 'valoresVariables'])
                        ->where('cotio_numcoti', $cotizacion->coti_num)
                        ->where('cotio_item', $item)
                        ->where('cotio_subitem', 0)
                        ->get()
                        ->keyBy('instance_number');

    // Obtener todos los responsables únicos de todas las tareas de la instancia actual
    $todosResponsablesTareas = collect();
    foreach ($tareasConInstancias as $tarea) {
        if ($tarea->instancia && $tarea->instancia->responsablesAnalisis) {
            $todosResponsablesTareas = $todosResponsablesTareas->merge($tarea->instancia->responsablesAnalisis);
        }
    }
    $todosResponsablesTareas = $todosResponsablesTareas->unique('usu_codigo');

    return view('ordenes.tareasporcategoria', [
        'cotizacion' => $cotizacion,
        'categoria' => $categoria,
        'tareas' => $tareasConInstancias,
        'usuarios' => $usuarios,
        'inventario' => $inventario,
        'instance' => $instance,
        'vehiculos' => $vehiculos,
        'instanciaActual' => $instanciaMuestra,
        'instanciasMuestra' => $instanciasMuestra,
        'variablesMuestra' => $variablesOrdenadas,

        'todosResponsablesTareas' => $todosResponsablesTareas
    ]);
}



public function asignarDetallesAnalisis(Request $request) 
{
    try {
        DB::beginTransaction();
        
        $actualizarRegistro = function($registro) use ($request) {
            // Actualizar vehículo si está en la solicitud
            if ($request->filled('vehiculo_asignado')) {
                $vehiculoAnterior = $registro->vehiculo_asignado;
                $nuevoVehiculo = $request->vehiculo_asignado;

                $registro->vehiculo_asignado = $nuevoVehiculo;
                if ($nuevoVehiculo) {
                    Vehiculo::where('id', $nuevoVehiculo)->update(['estado' => 'ocupado']);
                }

                if ($vehiculoAnterior && $vehiculoAnterior != $nuevoVehiculo) {
                    Vehiculo::where('id', $vehiculoAnterior)->update(['estado' => 'libre']);
                }
            }

            // Actualizar responsable si está en la solicitud
            if ($request->filled('responsable_codigo')) {
                $registro->responsable_analisis = $request->responsable_codigo === 'NULL' ? null : $request->responsable_codigo;
            }

            // Actualizar fechas si están en la solicitud
            if ($request->filled('fecha_inicio_ot')) {
                $registro->fecha_inicio_ot = $request->fecha_inicio_ot;
            }
            if ($request->filled('fecha_fin_ot')) {
                $registro->fecha_fin_ot = $request->fecha_fin_ot;
            }

            $registro->save();
        };

        $actualizarHerramientas = function($cotio_numcoti, $cotio_item, $cotio_subitem, $instance_number) use ($request) {
            if ($request->filled('herramientas')) {
                // Primero eliminamos todas las herramientas existentes para esta instancia
                DB::table('cotio_inventario_lab')
                    ->where('cotio_numcoti', $cotio_numcoti)
                    ->where('cotio_item', $cotio_item)
                    ->where('cotio_subitem', $cotio_subitem)
                    ->where('instance_number', $instance_number)
                    ->delete();

                // Insertamos las nuevas herramientas
                foreach ($request->herramientas as $herramientaId) {
                    DB::table('cotio_inventario_lab')->insert([
                        'cotio_numcoti' => $cotio_numcoti,
                        'cotio_item' => $cotio_item,
                        'cotio_subitem' => $cotio_subitem,
                        'instance_number' => $instance_number,
                        'inventario_lab_id' => $herramientaId,
                        'cantidad' => 1,
                        'observaciones' => null
                    ]);
                    
                    // Actualizamos el estado del inventario
                    InventarioLab::where('id', $herramientaId)->update(['estado' => 'ocupado']);
                }
            }
        };

        // 1. Actualizar la instancia de la muestra principal (subitem = 0)
        $instanciaMuestra = CotioInstancia::where('cotio_numcoti', $request->cotio_numcoti)
            ->where('cotio_item', $request->cotio_item)
            ->where('cotio_subitem', 0)
            ->where('instance_number', $request->instance_number)
            ->first();

        if ($instanciaMuestra) {
            $actualizarRegistro($instanciaMuestra);
            $actualizarHerramientas(
                $request->cotio_numcoti, 
                $request->cotio_item, 
                0, 
                $request->instance_number
            );
        }

        // 2. Actualizar instancias de tareas seleccionadas si existen
        if ($request->tareas_seleccionadas && count($request->tareas_seleccionadas) > 0) {
            foreach ($request->tareas_seleccionadas as $tarea) {
                $instanciaTarea = CotioInstancia::where('cotio_numcoti', $request->cotio_numcoti)
                    ->where('cotio_item', $tarea['item'])
                    ->where('cotio_subitem', $tarea['subitem'])
                    ->where('instance_number', $request->instance_number)
                    ->first();

                if ($instanciaTarea) {
                    $actualizarRegistro($instanciaTarea);
                    $actualizarHerramientas(
                        $request->cotio_numcoti, 
                        $tarea['item'], 
                        $tarea['subitem'], 
                        $request->instance_number
                    );
                } else {
                    // Si no existe la instancia, la creamos
                    $nuevaInstancia = CotioInstancia::create([
                        'cotio_numcoti' => $request->cotio_numcoti,
                        'cotio_item' => $tarea['item'],
                        'cotio_subitem' => $tarea['subitem'],
                        'instance_number' => $request->instance_number,
                        'responsable_analisis' => $request->responsable_codigo === 'NULL' ? null : $request->responsable_codigo,
                        'fecha_inicio_ot' => $request->fecha_inicio_ot,
                        'fecha_fin_ot' => $request->fecha_fin_ot,
                        'vehiculo_asignado' => $request->vehiculo_asignado,
                        'active_ot' => true
                    ]);

                    if ($request->filled('herramientas')) {
                        $actualizarHerramientas(
                            $request->cotio_numcoti, 
                            $tarea['item'], 
                            $tarea['subitem'], 
                            $request->instance_number
                        );
                    }
                }
            }
        }

        DB::commit();
        return response()->json([
            'success' => true, 
            'message' => 'Elementos asignados correctamente a las instancias'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar las instancias: ' . $e->getMessage(),
            'error_details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ], 500);
    }
}


public function pasarAnalisis(Request $request)
{
    try {
        $cotizacionId = $request->cotizacion_id;
        $cambios = $request->cambios;

        // Verificar si ya existen instancias activas para los análisis seleccionados
        foreach ($cambios as $cambio) {
            $instanciaExistente = CotioInstancia::where([
                'cotio_numcoti' => $cotizacionId,
                'cotio_item' => $cambio['item'],
                'cotio_subitem' => $cambio['subitem'],
                'instance_number' => $cambio['instance'],
                'active_ot' => true
            ])->first();

            if ($instanciaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => "El análisis ya está activo en la instancia {$cambio['instance']}. Por favor, desactive la instancia actual antes de crear una nueva."
                ]);
            }
        }

        DB::beginTransaction();

        foreach ($cambios as $cambio) {
            // Crear nueva instancia para el análisis
            $instancia = new CotioInstancia();
            $instancia->cotio_numcoti = $cotizacionId;
            $instancia->cotio_item = $cambio['item'];
            $instancia->cotio_subitem = $cambio['subitem'];
            $instancia->instance_number = $cambio['instance'];
            $instancia->active_ot = $cambio['activo'];
            $instancia->cotio_estado_analisis = 'pendiente';
            $instancia->save();

            // Actualizar estado en la tabla cotio
            Cotio::where([
                'cotio_numcoti' => $cotizacionId,
                'cotio_item' => $cambio['item'],
                'cotio_subitem' => $cambio['subitem']
            ])->update([
                'cotio_estado_analisis' => 'pendiente'
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Análisis pasados correctamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al pasar a análisis: ' . $e->getMessage()
        ]);
    }
}


public function showOrdenesAll($cotio_numcoti, $cotio_item, $cotio_subitem = 0, $instance = null)
{
    $instance = $instance ?? 1;
    $usuarioActual = trim(Auth::user()->usu_codigo);
    $allHerramientas = InventarioLab::all();

    try {
        // Depurar datos en instancia_responsable_analisis
        $responsablesAsignados = DB::table('instancia_responsable_analisis')
            ->join('cotio_instancias', 'instancia_responsable_analisis.cotio_instancia_id', '=', 'cotio_instancias.id')
            ->where('cotio_instancias.cotio_numcoti', $cotio_numcoti)
            ->where('cotio_instancias.cotio_item', $cotio_item)
            ->where('cotio_instancias.instance_number', $instance)
            ->select('instancia_responsable_analisis.usu_codigo', 'cotio_instancias.id', 'cotio_instancias.cotio_subitem')
            ->get();

        Log::debug('Responsables asignados encontrados', [
            'cotio_numcoti' => $cotio_numcoti,
            'cotio_item' => $cotio_item,
            'instance' => $instance,
            'responsables' => $responsablesAsignados->map(function ($item) {
                return ['usu_codigo' => $item->usu_codigo, 'instancia_id' => $item->id, 'cotio_subitem' => $item->cotio_subitem];
            })->toArray()
        ]);

        // Obtener la instancia de muestra principal sin exigir responsable directo
        $instanciaMuestra = CotioInstancia::with([
            'muestra.vehiculo',
            'muestra.cotizacion',
            'valoresVariables' => function ($query) {
                $query->select('id', 'cotio_instancia_id', 'variable', 'valor')
                      ->orderBy('variable');
            },
            'responsablesAnalisis',
            'herramientasLab' => function ($query) {
                $query->select('inventario_lab.*', 'cotio_inventario_lab.cantidad', 
                              'cotio_inventario_lab.observaciones as pivot_observaciones');
            }
        ])
        ->where('cotio_numcoti', $cotio_numcoti)
        ->where('cotio_item', $cotio_item)
        ->where('cotio_subitem', 0)
        ->where('instance_number', $instance)
        ->first();

        if (!$instanciaMuestra) {
            Log::warning('No se encontró instancia de muestra', [
                'user' => $usuarioActual,
                'cotio_numcoti' => $cotio_numcoti,
                'cotio_item' => $cotio_item,
                'instance' => $instance
            ]);
            return view('mis-ordenes.show-by-categoria', [
                'instancia' => null,
                'analisis' => collect(),
                'instanceNumber' => $instance,
                'allHerramientas' => $allHerramientas,
                'error' => 'No se encontró la muestra principal.'
            ]);
        }

        Log::debug('Instancia de muestra encontrada', [
            'instancia_id' => $instanciaMuestra->id,
            'responsables' => $instanciaMuestra->responsablesAnalisis->pluck('usu_codigo')->toArray()
        ]);

        // Obtener análisis asignados al usuario
        $analisis = CotioInstancia::with([
            'tarea.vehiculo',
            'tarea.cotizacion',
            'responsablesAnalisis',
            'herramientasLab' => function ($query) {
                $query->select('inventario_lab.*', 'cotio_inventario_lab.cantidad', 
                              'cotio_inventario_lab.observaciones as pivot_observaciones');
            }
        ])
        ->where('cotio_numcoti', $cotio_numcoti)
        ->where('cotio_item', $cotio_item)
        ->where('cotio_subitem', '>', 0)
        ->where('active_ot', true)
        ->where('instance_number', $instance)
        ->whereHas('responsablesAnalisis', function ($query) use ($usuarioActual) {
            $query->whereRaw('TRIM(instancia_responsable_analisis.usu_codigo) = ?', [$usuarioActual]);
        })
        ->orderBy('cotio_subitem')
        ->get();

        Log::debug('Análisis encontrados', [
            'count' => $analisis->count(),
            'instancia_ids' => $analisis->pluck('id')->toArray(),
            'responsables' => $analisis->map(function ($item) {
                return $item->responsablesAnalisis->pluck('usu_codigo')->toArray();
            })->toArray()
        ]);

        return view('mis-ordenes.show-by-categoria', [
            'instancia' => $instanciaMuestra,
            'analisis' => $analisis,
            'instanceNumber' => $instance,
            'allHerramientas' => $allHerramientas
        ]);

    } catch (\Exception $e) {
        Log::error('Error al mostrar órdenes', [
            'user' => $usuarioActual,
            'cotio_numcoti' => $cotio_numcoti,
            'cotio_item' => $cotio_item,
            'cotio_subitem' => $cotio_subitem,
            'instance' => $instance,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return view('mis-ordenes.show-by-categoria', [
            'instancia' => null,
            'analisis' => collect(),
            'instanceNumber' => $instance,
            'allHerramientas' => $allHerramientas,
            'error' => 'Error al cargar la muestra: ' . $e->getMessage()
        ]);
    }
}



public function updateHerramientas(Request $request, $instanciaId)
{
    $instancia = CotioInstancia::findOrFail($instanciaId);

    $request->validate([
        'herramientas' => 'nullable|array',
        'herramientas.*' => 'exists:inventario_lab,id',
        'cantidades' => 'nullable|array',
        'cantidades.*' => 'integer|min:1',
        'observaciones' => 'nullable|array',
    ]);

    $herramientasData = [];
    if ($request->herramientas) {
        foreach ($request->herramientas as $herramientaId) {
            $herramientasData[$herramientaId] = [
                'cantidad' => $request->cantidades[$herramientaId] ?? 1,
                'observaciones' => $request->observaciones[$herramientaId] ?? null,
                'cotio_numcoti' => $instancia->cotio_numcoti,
                'cotio_item' => $instancia->cotio_item,
                'cotio_subitem' => $instancia->cotio_subitem,
                'instance_number' => $instancia->instance_number
            ];
        }
    }

    $instancia->herramientasLab()->sync($herramientasData);

    return response()->json([
        'success' => true,
        'message' => 'Estado actualizado correctamente'
    ]);
}





public function asignacionMasiva(Request $request, $ordenId)
{
    Log::info('Iniciando asignación masiva', [
        'ordenId' => $ordenId,
        'user' => Auth::user()->usu_codigo ?? 'unknown',
        'request' => $request->all()
    ]);

    $cotizacion = Coti::findOrFail($ordenId);

    $validated = $request->validate([
        'instancia_selecciones' => 'required_without:tarea_selecciones|array',
        'instancia_selecciones.*' => 'string',
        'tarea_selecciones' => 'required_without:instancia_selecciones|array',
        'tarea_selecciones.*' => 'string',
        'responsables_analisis' => 'nullable|array',
        'responsables_analisis.*' => 'exists:usu,usu_codigo',
        'herramientas_lab' => 'nullable|array',
        'herramientas_lab.*' => 'exists:inventario_lab,id',
        'fecha_inicio_ot' => 'nullable|date',
        'fecha_fin_ot' => 'nullable|date|after:fecha_inicio_ot',
        'aplicar_a_gemelas' => 'boolean'
    ]);

    DB::beginTransaction();
    try {
        $instanciaSelecciones = $validated['instancia_selecciones'] ?? [];
        $tareaSelecciones = $validated['tarea_selecciones'] ?? [];
        $herramientasLab = $validated['herramientas_lab'] ?? [];
        $responsablesAnalisis = array_map('trim', $validated['responsables_analisis'] ?? []);
        $aplicarAGemelas = $validated['aplicar_a_gemelas'] ?? false;
        $updatedCount = 0;

        Log::debug('Datos validados', [
            'instancia_selecciones' => $instanciaSelecciones,
            'tarea_selecciones' => $tareaSelecciones,
            'responsables_analisis' => $responsablesAnalisis,
            'herramientas_lab' => $herramientasLab,
            'aplicar_a_gemelas' => $aplicarAGemelas
        ]);

        // 1. Obtener todos los usuarios de los sectores seleccionados
        $usuariosDelSector = collect();
        foreach ($responsablesAnalisis as $responsableCodigo) {
            $responsable = User::where('usu_codigo', $responsableCodigo)->first();
            if (!$responsable) {
                Log::error('Usuario no encontrado', ['responsable_codigo' => $responsableCodigo]);
                throw new \Exception("Usuario con código '$responsableCodigo' no encontrado.");
            }

            // Si el usuario es un líder de sector (LAB, LAB1), obtenemos sus miembros
            if ($responsable->miembros()->exists()) {
                $miembros = $responsable->miembros()->pluck('usu_codigo')->toArray();
                $usuariosDelSector = $usuariosDelSector->merge($responsable->miembros);
                Log::debug('Miembros del sector encontrados', [
                    'sector' => $responsableCodigo,
                    'miembros' => $miembros
                ]);
            }
            // Siempre incluimos al propio responsable (LAB/LAB1)
            $usuariosDelSector->push($responsable);
        }

        // Eliminar duplicados y obtener solo los códigos
        $usuariosASincronizar = $usuariosDelSector->unique('usu_codigo')
            ->pluck('usu_codigo')
            ->map('trim')
            ->toArray();

        Log::info('Usuarios a sincronizar', [
            'usuarios' => $usuariosASincronizar,
            'total' => count($usuariosASincronizar)
        ]);

        // 2. Obtener todas las instancias seleccionadas
        $instanciasSeleccionadas = CotioInstancia::with(['muestra'])
            ->whereIn('id', array_merge($instanciaSelecciones, $tareaSelecciones))
            ->get();

        Log::debug('Instancias seleccionadas', [
            'count' => $instanciasSeleccionadas->count(),
            'ids' => $instanciasSeleccionadas->pluck('id')->toArray()
        ]);

        // 3. Crear mapa de muestras a análisis seleccionados
        $mapaSelecciones = $this->crearMapaSelecciones($instanciasSeleccionadas);
        Log::debug('Mapa de selecciones creado', [
            'mapa' => array_keys($mapaSelecciones)
        ]);

        // 4. Actualizar muestras relacionadas
        $muestrasActualizadas = collect();
        foreach ($instanciasSeleccionadas as $instancia) {
            if ($instancia->cotio_subitem > 0) {
                $muestra = CotioInstancia::where([
                    'cotio_numcoti' => $instancia->cotio_numcoti,
                    'cotio_item' => $instancia->cotio_item,
                    'instance_number' => $instancia->instance_number,
                    'cotio_subitem' => 0
                ])->first();

                if ($muestra && !$muestrasActualizadas->contains($muestra->id)) {
                    $muestra->active_ot = true;
                    $muestra->cotio_estado_analisis = 'coordinado analisis';
                    $muestra->save();
                    $muestrasActualizadas->push($muestra->id);
                    $updatedCount++;

                    Log::info('Muestra actualizada', [
                        'muestra_id' => $muestra->id,
                        'cotio_numcoti' => $instancia->cotio_numcoti,
                        'cotio_item' => $instancia->cotio_item,
                        'instance_number' => $instancia->instance_number
                    ]);

                    if ($aplicarAGemelas) {
                        foreach ($muestra->gemelos() as $muestraGemela) {
                            $muestraGemela->active_ot = true;
                            $muestraGemela->cotio_estado_analisis = 'coordinado analisis';
                            $muestraGemela->save();
                            $updatedCount++;
                            Log::debug('Muestra gemela actualizada', [
                                'gemela_id' => $muestraGemela->id,
                                'cotio_numcoti' => $instancia->cotio_numcoti,
                                'cotio_item' => $instancia->cotio_item,
                                'instance_number' => $instancia->instance_number
                            ]);
                        }
                    }
                }
            }
        }

        Log::info('Muestras actualizadas', [
            'count' => $muestrasActualizadas->count(),
            'ids' => $muestrasActualizadas->toArray()
        ]);

        // 5. Procesar cada instancia seleccionada
        foreach ($instanciasSeleccionadas as $instancia) {
            $esAnalisisSeleccionado = $this->esInstanciaSeleccionada(
                $instancia,
                $instanciaSelecciones,
                $tareaSelecciones
            );

            $countBefore = $updatedCount;
            $updatedCount += $this->procesarInstancia(
                $instancia,
                $validated,
                $herramientasLab,
                $usuariosASincronizar,
                $esAnalisisSeleccionado,
                $mapaSelecciones
            );

            if ($updatedCount > $countBefore) {
                Log::debug('Instancia procesada', [
                    'instancia_id' => $instancia->id,
                    'es_analisis' => $esAnalisisSeleccionado,
                    'cotio_numcoti' => $instancia->cotio_numcoti,
                    'cotio_item' => $instancia->cotio_item,
                    'cotio_subitem' => $instancia->cotio_subitem
                ]);
            }

            if ($aplicarAGemelas) {
                foreach ($instancia->gemelos() as $gemelo) {
                    $countBeforeGemela = $updatedCount;
                    $updatedCount += $this->procesarInstanciaGemela(
                        $gemelo,
                        $validated,
                        $herramientasLab,
                        $usuariosASincronizar,
                        $mapaSelecciones,
                        $instancia
                    );

                    if ($updatedCount > $countBeforeGemela) {
                        Log::debug('Instancia gemela procesada', [
                            'gemela_id' => $gemelo->id,
                            'cotio_numcoti' => $gemelo->cotio_numcoti,
                            'cotio_item' => $gemelo->cotio_item,
                            'cotio_subitem' => $gemelo->cotio_subitem
                        ]);
                    }
                }
            }
        }

        DB::commit();

        Log::info('Asignación masiva completada', [
            'ordenId' => $ordenId,
            'updated_count' => $updatedCount,
            'usuarios_sincronizados' => $usuariosASincronizar,
            'instancias_procesadas' => $instanciasSeleccionadas->pluck('id')->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asignación masiva completada para ' . $updatedCount . ' instancias',
            'updated_count' => $updatedCount
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error en asignación masiva', [
            'ordenId' => $ordenId,
            'user' => Auth::user()->usu_codigo ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error en asignación masiva: ' . $e->getMessage(),
            'error' => $e->getTraceAsString()
        ], 500);
    }
}

protected function procesarInstancia(
    CotioInstancia $instancia, 
    array $validated, 
    array $herramientasLab, 
    array $usuariosASincronizar,
    bool $esSeleccionada,
    array $mapaSelecciones
): int {
    $updatedCount = 0;

    if ($esSeleccionada || ($instancia->cotio_subitem == 0 && isset($mapaSelecciones[$instancia->id]))) {
        $this->actualizarInstancia($instancia, $validated);
        $this->asignarHerramientas($instancia, $herramientasLab);
        
        if (!empty($usuariosASincronizar)) {
            $instancia->responsablesAnalisis()->sync($usuariosASincronizar);
        }
        $updatedCount++;
    }

    return $updatedCount;
}


/**
 * Crea un mapa de muestras a análisis seleccionados
 */
protected function crearMapaSelecciones($instanciasSeleccionadas)
{
    $mapa = [];
    
    foreach ($instanciasSeleccionadas as $instancia) {
        if ($instancia->cotio_subitem == 0) { // Es una muestra
            $mapa[$instancia->id] = [
                'muestra' => $instancia,
                'analisis_ids' => $instancia->tareas->pluck('id')->toArray()
            ];
        } else { // Es un análisis
            $muestra = $instancia->muestra;
            if ($muestra) {
                if (!isset($mapa[$muestra->id])) {
                    $mapa[$muestra->id] = [
                        'muestra' => $muestra,
                        'analisis_ids' => []
                    ];
                }
                $mapa[$muestra->id]['analisis_ids'][] = $instancia->id;
            }
        }
    }
    
    return $mapa;
}

/**
 * Determina si una instancia fue seleccionada directamente
 */
protected function esInstanciaSeleccionada($instancia, $instanciaSelecciones, $tareaSelecciones)
{
    return in_array($instancia->id, $instanciaSelecciones) || 
           in_array($instancia->id, $tareaSelecciones);
}


/**
 * Procesa una instancia gemela
 */
protected function procesarInstanciaGemela(
    CotioInstancia $gemelo, 
    array $validated, 
    array $herramientasLab, 
    array $responsablesAnalisis,
    array $mapaSelecciones,
    CotioInstancia $instanciaOriginal
): int {
    $updatedCount = 0;

    // Si la original es una muestra con análisis seleccionados
    if ($instanciaOriginal->cotio_subitem == 0 && isset($mapaSelecciones[$instanciaOriginal->id])) {
        // Actualizar la muestra gemela
        $this->actualizarInstancia($gemelo, $validated);
        $this->asignarHerramientas($gemelo, $herramientasLab);
        
        if (!empty($responsablesAnalisis)) {
            $gemelo->responsablesAnalisis()->sync($responsablesAnalisis);
        }
        $updatedCount++;

        // Obtener los análisis gemelos correspondientes a los seleccionados en la original
        $analisisSeleccionadosOriginal = $mapaSelecciones[$instanciaOriginal->id]['analisis_ids'];
        $subitemsSeleccionados = CotioInstancia::whereIn('id', $analisisSeleccionadosOriginal)
            ->pluck('cotio_subitem')
            ->unique()
            ->toArray();

        // Actualizar solo los análisis gemelos con los mismos subitems que los seleccionados
        foreach ($gemelo->tareas as $analisisGemelo) {
            if (in_array($analisisGemelo->cotio_subitem, $subitemsSeleccionados)) {
                $this->actualizarInstancia($analisisGemelo, $validated);
                $this->asignarHerramientas($analisisGemelo, $herramientasLab);
                
                if (!empty($responsablesAnalisis)) {
                    $analisisGemelo->responsablesAnalisis()->sync($responsablesAnalisis);
                }
                $updatedCount++;
            }
        }
    }
    // Si la original es un análisis seleccionado directamente
    elseif ($instanciaOriginal->cotio_subitem > 0) {
        // Actualizar solo el análisis gemelo correspondiente
        $this->actualizarInstancia($gemelo, $validated);
        $this->asignarHerramientas($gemelo, $herramientasLab);
        
        if (!empty($responsablesAnalisis)) {
            $gemelo->responsablesAnalisis()->sync($responsablesAnalisis);
        }
        $updatedCount++;
    }

    return $updatedCount;
}


protected function actualizarInstancia(CotioInstancia $instancia, array $validated)
{
    $instancia->active_ot = true;
    $instancia->cotio_estado_analisis = 'coordinado analisis';

    if (isset($validated['responsable_codigo'])) {
        $instancia->responsable_analisis = $validated['responsable_codigo'] === 'NULL' ? null : $validated['responsable_codigo'];
    }

    if (!empty($validated['fecha_inicio_ot'])) {
        $instancia->fecha_inicio_ot = $validated['fecha_inicio_ot'];
    }

    if (!empty($validated['fecha_fin_ot'])) {
        $instancia->fecha_fin_ot = $validated['fecha_fin_ot'];
    }

    $instancia->save();
}

protected function asignarHerramientas(CotioInstancia $instancia, array $herramientasLab)
{
    if (!empty($herramientasLab)) {
        $syncData = [];
        foreach ($herramientasLab as $herramientaId) {
            $exists = DB::table('cotio_inventario_lab')
                ->where([
                    'cotio_numcoti' => $instancia->cotio_numcoti,
                    'cotio_item' => $instancia->cotio_item,
                    'cotio_subitem' => $instancia->cotio_subitem,
                    'instance_number' => $instancia->instance_number,
                    'inventario_lab_id' => $herramientaId,
                ])
                ->exists();

            if (!$exists) {
                $syncData[$herramientaId] = [
                    'cotio_numcoti' => $instancia->cotio_numcoti,
                    'cotio_item' => $instancia->cotio_item,
                    'cotio_subitem' => $instancia->cotio_subitem,
                    'instance_number' => $instancia->instance_number,
                    'cantidad' => 1,
                    'observaciones' => null,
                ];
            }
        }
        if (!empty($syncData)) {
            $instancia->herramientasLab()->syncWithoutDetaching($syncData);
            Log::debug('Asignando herramientas', [
                'instancia_id' => $instancia->id,
                'cotio_numcoti' => $instancia->cotio_numcoti,
                'cotio_item' => $instancia->cotio_item,
                'cotio_subitem' => $instancia->cotio_subitem,
                'instance_number' => $instancia->instance_number,
                'herramientas' => array_keys($syncData)
            ]);
        }
    } else {
        $instancia->herramientasLab()->detach();
        Log::debug('Eliminando herramientas', [
            'instancia_id' => $instancia->id,
            'cotio_numcoti' => $instancia->cotio_numcoti,
            'cotio_item' => $instancia->cotio_item,
            'cotio_subitem' => $instancia->cotio_subitem,
            'instance_number' => $instancia->instance_number
        ]);
    }
}

protected function resolveInstancia($key)
{
    // If key is a single ID
    if (is_numeric($key)) {
        return CotioInstancia::find($key);
    }

    // If key is composite (numcoti_item_subitem_instance)
    $parts = explode('_', $key);
    if (count($parts) === 4) {
        [$numcoti, $item, $subitem, $instance] = $parts;
        return CotioInstancia::where([
            'cotio_numcoti' => $numcoti,
            'cotio_item' => $item,
            'cotio_subitem' => $subitem,
            'instance_number' => $instance,
            'enable_ot' => true
        ])->first();
    }

    return null;
}

protected function getInstanciasGemelas(CotioInstancia $instancia)
{
    return CotioInstancia::where([
        'cotio_numcoti' => $instancia->cotio_numcoti,
        'cotio_item' => $instancia->cotio_item,
        'cotio_subitem' => $instancia->cotio_subitem,
        'enable_ot' => true
    ])
    ->where('instance_number', '!=', $instancia->instance_number)
    ->get();
}




public function removerResponsable(Request $request, $ordenId)
{
    $validated = $request->validate([
        'instancia_id' => 'required|integer|exists:cotio_instancias,id',
        'user_codigo' => 'required|string|exists:usu,usu_codigo',
        'todos' => 'required|string|in:true,false' // Validar como string primero
    ]);

    // Convertir el string 'true'/'false' a booleano
    $todos = $validated['todos'] === 'true';

    try {
        DB::beginTransaction();

        $instancia = CotioInstancia::findOrFail($validated['instancia_id']);
        $userCodigo = $validated['user_codigo'];

        if ($todos) {
            // Encontrar todas las instancias (muestra y tareas) con mismo cotio_numcoti, cotio_item, instance_number
            $instancias = CotioInstancia::where([
                'cotio_numcoti' => $instancia->cotio_numcoti,
                'cotio_item' => $instancia->cotio_item,
                'instance_number' => $instancia->instance_number,
            ])->get();


            $totalEliminados = 0;
            // Eliminar usuario de instancia_responsable_analisis para todas las instancias coincidentes
            foreach ($instancias as $inst) {
                // Verificar qué responsables están asignados a esta instancia
                $responsablesActuales = DB::table('instancia_responsable_analisis')
                    ->where('cotio_instancia_id', $inst->id)
                    ->get();
                
                // Eliminar de análisis
                $deletedAnalisis = DB::table('instancia_responsable_analisis')
                    ->where('cotio_instancia_id', $inst->id)
                    ->whereRaw('TRIM(usu_codigo) = ?', [$userCodigo])
                    ->delete();
                
                // También eliminar de muestreo por si está ahí
                $deletedMuestreo = DB::table('instancia_responsable_muestreo')
                    ->where('cotio_instancia_id', $inst->id)
                    ->whereRaw('TRIM(usu_codigo) = ?', [$userCodigo])
                    ->delete();
                
                $totalEliminados += $deletedAnalisis + $deletedMuestreo;
            }

        } else {
            // Eliminar usuario solo de la instancia especificada
            $deletedAnalisis = DB::table('instancia_responsable_analisis')
                ->where('cotio_instancia_id', $validated['instancia_id'])
                ->whereRaw('TRIM(usu_codigo) = ?', [$userCodigo])
                ->delete();

            $deletedMuestreo = DB::table('instancia_responsable_muestreo')
                ->where('cotio_instancia_id', $validated['instancia_id'])
                ->whereRaw('TRIM(usu_codigo) = ?', [$userCodigo])
                ->delete();
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Responsable eliminado correctamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar responsable: ' . $e->getMessage()
        ], 500);
    }
}



public function enableInforme(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required|integer|exists:cotio_instancias,cotio_numcoti',
        'cotio_item' => 'required|integer',
        'cotio_subitem' => 'required|integer',
        'instance' => 'required|integer',
    ]);

    try {
        $instancia = CotioInstancia::where([
            'cotio_numcoti' => $request->cotio_numcoti,
            'cotio_item' => $request->cotio_item,
            'cotio_subitem' => $request->cotio_subitem,
            'instance_number' => $request->instance,
        ])->firstOrFail();

        if ($instancia->cotio_estado_analisis !== 'analizado') {
            return response()->json([
                'success' => false,
                'message' => 'La instancia no está en estado analizado.',
            ], 400);
        }

        DB::beginTransaction();
        $instancia->enable_inform = true;
        $instancia->fecha_creacion_inform = now();
        $instancia->save();
        DB::commit();

        return redirect()->back()->with('success', 'Informe habilitado exitosamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al habilitar el informe: ' . $e->getMessage());
    }
}

public function disableInforme(Request $request)
{
    $request->validate([
        'cotio_numcoti' => 'required|integer|exists:cotio_instancias,cotio_numcoti',
        'cotio_item' => 'required|integer',
        'cotio_subitem' => 'required|integer',
        'instance' => 'required|integer',
    ]);

    try {
        $instancia = CotioInstancia::where([
            'cotio_numcoti' => $request->cotio_numcoti,
            'cotio_item' => $request->cotio_item,
            'cotio_subitem' => $request->cotio_subitem, 
            'instance_number' => $request->instance,
        ])->firstOrFail();

        DB::beginTransaction();
        $instancia->enable_inform = false;
        $instancia->fecha_creacion_inform = null;
        $instancia->save();
        DB::commit();

        return redirect()->back()->with('success', 'Informe deshabilitado exitosamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al deshabilitar el informe: ' . $e->getMessage());
    }
}




public function finalizarTodas(Request $request)
{

    try {
        $request->validate([
            'cotio_numcoti' => 'required',
            'cotio_item' => 'required',
            'cotio_subitem' => 'required',
            'instance_number' => 'required',
        ]);

        $params = [
            'cotio_numcoti' => $request->cotio_numcoti,
            'cotio_item' => $request->cotio_item,
            'instance_number' => $request->instance_number,
            'active_ot' => true
        ];


        $instancias = CotioInstancia::where($params)
            ->where(function($query) use ($request) {
                $query->where('cotio_subitem', $request->cotio_subitem) // Muestra principal
                      ->orWhere('cotio_subitem', '>', 0); // Análisis asociados
            })
            ->get();


        if ($instancias->isEmpty()) {
            return redirect()->back()->with('info', 'No hay muestras o análisis activos para finalizar.');
        }

        $updatedCount = 0;
        foreach ($instancias as $instancia) {
            
            $result = $instancia->update([
                'cotio_estado_analisis' => 'analizado',
            ]);

            if ($result) {
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', 'Todas las muestras y análisis activos han sido finalizados correctamente.');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al finalizar muestras y análisis: ' . $e->getMessage());
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
        'fecha_carga_ot' => 'nullable|date',
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

        if(Auth::user()->rol == 'coordinador_lab' || Auth::user()->usu_nivel >= '900') {
            $item->cotio_estado_analisis = $validated['estado'];
            
            // Actualizar la fecha de carga OT si se proporcionó
            if (isset($validated['fecha_carga_ot']) && $validated['fecha_carga_ot']) {
                $item->fecha_carga_ot = $validated['fecha_carga_ot'];
            } elseif ($validated['estado'] === 'analizado' && !$item->fecha_carga_ot) {
                // Si el estado es 'analizado' y no hay fecha de carga, establecer la fecha actual
                $item->fecha_carga_ot = now();
            }
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

                InventarioLab::whereIn('id', $herramientasAsignadas)
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




public function apiHerramientasInstancia($instanciaId)
{
    $instancia = \App\Models\CotioInstancia::findOrFail($instanciaId);
    $todasHerramientas = \App\Models\InventarioLab::all();
    $herramientasAsignadas = $instancia->herramientasLab ? $instancia->herramientasLab->pluck('id')->toArray() : [];

    $data = $todasHerramientas->map(function($h) use ($herramientasAsignadas) {
        return [
            'id' => $h->id,
            'nombre' => $h->equipamiento . ($h->marca_modelo ? ' (' . $h->marca_modelo . ')' : ''),
            'asignada' => in_array($h->id, $herramientasAsignadas),
        ];
    });

    return response()->json(['herramientas' => $data]);
}





public function deshacerAsignaciones(Request $request)
{
    try {
        $instanciaId = $request->instancia_id;
        $cotizacionId = $request->cotizacion_id;
        $currentUser = Auth::user();

        DB::beginTransaction();

        // Obtener la instancia de la muestra
        $instanciaMuestra = CotioInstancia::findOrFail($instanciaId);

        // Verificar que sea una instancia de muestra (subitem = 0)
        if ($instanciaMuestra->cotio_subitem !== 0) {
            throw new \Exception('Solo se pueden deshacer asignaciones de muestras');
        }

        // Obtener todas las instancias de análisis asociadas
        $instanciasAnalisis = CotioInstancia::where([
            'cotio_numcoti' => $cotizacionId,
            'cotio_item' => $instanciaMuestra->cotio_item,
            'instance_number' => $instanciaMuestra->instance_number
        ])->where('cotio_subitem', '!=', 0)->get();

        // 1. Eliminar notificaciones relacionadas con esta muestra y sus análisis
        $idsInstancias = $instanciasAnalisis->pluck('id')->push($instanciaMuestra->id);
        
        DB::table('simple_notifications')
            ->whereIn('instancia_id', $idsInstancias)
            ->delete();

        // 2. Desactivar todas las instancias de análisis asociadas
        foreach ($instanciasAnalisis as $instancia) {
            $instancia->update([
                'active_ot' => false,
                'cotio_estado_analisis' => null,
                'coordinador_codigo' => null,
                'fecha_coordinacion' => null,
                'fecha_inicio_ot' => null,
                'fecha_fin_ot' => null
            ]);

            DB::table('instancia_responsable_analisis')
                ->where('cotio_instancia_id', $instancia->id)
                ->delete();
                
            DB::table('cotio_inventario_lab')
                ->where('cotio_instancia_id', $instancia->id)
                ->delete();
        }

        // 3. Desactivar la instancia principal
        $instanciaMuestra->update([
            'active_ot' => false,
            'cotio_estado_analisis' => null,
            'coordinador_codigo' => null,
            'fecha_coordinacion' => null,
            'fecha_inicio_ot' => null,
            'fecha_fin_ot' => null
        ]);
        
        DB::table('instancia_responsable_analisis')
            ->where('cotio_instancia_id', $instanciaMuestra->id)
            ->delete();
            
        DB::table('cotio_inventario_lab')
            ->where('cotio_instancia_id', $instanciaMuestra->id)
            ->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Asignaciones deshechas y notificaciones eliminadas correctamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al deshacer asignaciones: ' . $e->getMessage()
        ]);
    }
}
}