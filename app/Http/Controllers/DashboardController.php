<?php

namespace App\Http\Controllers;

use App\Models\Coti;
use App\Models\CotioInstancia;
use App\Models\Vehiculo;
use App\Models\InventarioMuestreo;
use App\Models\InventarioLab;
use App\Models\Informes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{



public function index()
{
    // Resumen general
    $totalCotizaciones = Coti::count();
    $cotizacionesRecientes = Coti::orderBy('coti_fechaalta', 'desc')->take(5)->get();
    
    // Estadísticas de muestreo
    $muestrasTotales = CotioInstancia::where('cotio_subitem', 0)->count();
    $muestrasPendientes = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'coordinado muestreo')->count();
    $muestrasEnProceso = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'en revision muestreo')->count();
    $muestrasFinalizadas = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'muestreado')->count();
    
    // Estadísticas de análisis
    $analisisTotales = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)->count();  
    $analisisPendientes = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado_analisis', 'coordinado analisis')->count();
    $analisisEnProceso = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado_analisis', 'en revision analisis')->count();
    $analisisFinalizados = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado_analisis', 'analizado')->count();
    
    // Muestras próximas a vencer
    $muestrasProximas = CotioInstancia::where('cotio_subitem', 0)
        ->where('fecha_fin_muestreo', '>=', now())
        ->where('fecha_fin_muestreo', '<=', now()->addDays(7))
        ->orderBy('fecha_fin_muestreo')
        ->get();
    
    // Vehículos en uso
    $vehiculosOcupados = Vehiculo::where('estado', 'ocupado')->with('cotioInstancias')->get();
    
    // Informes
    $informesTotales = CotioInstancia::where('cotio_subitem', 0)->where('enable_inform', true)->count();
    
    return view('dashboard.admin', compact(
        'totalCotizaciones',
        'cotizacionesRecientes',
        'muestrasTotales',
        'muestrasPendientes',
        'muestrasEnProceso',
        'muestrasFinalizadas',
        'analisisTotales',
        'analisisPendientes',
        'analisisEnProceso',
        'analisisFinalizados',
        'muestrasProximas',
        'vehiculosOcupados',
        'informesTotales'
    ));
}


public function dashboardMuestreo(Request $request)
{
    $userCodigo = Auth::user()->usu_codigo;
    $estadoFiltro = $request->get('estado', 'all');

    // Base query para muestras
    $query = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where(function($query) use ($userCodigo) {
            $query->where('cotio_instancias.coordinador_codigo', $userCodigo)
                  ->orWhereHas('responsablesMuestreo', function($q) use ($userCodigo) {
                      $q->where('instancia_responsable_muestreo.usu_codigo', $userCodigo);
                  });
        });

    // Aplicar filtro de estado si no es 'all'
    if ($estadoFiltro !== 'all') {
        $query->where('cotio_estado', $estadoFiltro);
    }

    // Obtener muestras con paginación
    $muestras = $query->with(['cotizacion', 'vehiculo', 'responsablesMuestreo'])
        ->orderBy('cotio_instancias.fecha_fin_muestreo')
        ->paginate(10);
    
    // Estadísticas (sin filtro de estado)
    $totalMuestras = CotioInstancia::where('cotio_subitem', 0)
        // ->where('enable_muestreo', true)
        ->count();
    
    $pendientes = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'coordinado muestreo')
        // ->where('enable_muestreo', true)
        ->count();
    
    $enProceso = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'en revision muestreo')
        // ->where('enable_muestreo', true)
        ->count();
    
    $finalizadas = CotioInstancia::where('cotio_subitem', 0)
        ->where('cotio_estado', 'muestreado')
        // ->where('enable_muestreo', true)
        ->count();
    
    
    // Muestras próximas
    $muestrasProximas = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('fecha_inicio_muestreo', '>=', now())
        ->where('fecha_inicio_muestreo', '<=', now()->addDays(3))
        ->with(['cotizacion', 'vehiculo'])
        ->orderBy('fecha_inicio_muestreo')
        ->get();
    
    // Vehículos asignados
    $vehiculosAsignados = Vehiculo::whereIn('id', 
        $muestras->whereNotNull('vehiculo_asignado')->pluck('vehiculo_asignado')->unique()
    )->get();
    
    // Herramientas en uso
    $herramientasEnUso = InventarioMuestreo::whereHas('cotioInstancias', function($q) {
        $q->where('cotio_instancias.cotio_subitem', 0)
          ->where('cotio_instancias.cotio_estado', '!=', 'finalizado');
    })->withCount(['cotioInstancias' => function($q) {
        $q->where('cotio_instancias.cotio_subitem', 0)
          ->where('cotio_instancias.cotio_estado', '!=', 'finalizado');
    }])->get();
    
    return view('dashboard.muestreo', compact(
        'muestras',
        'totalMuestras',
        'pendientes',
        'enProceso',
        'finalizadas',
        'muestrasProximas',
        'vehiculosAsignados',
        'herramientasEnUso',
        'estadoFiltro'
    ));
}



public function dashboardAnalisis(Request $request)
{
    $userCodigo = Auth::user()->usu_codigo;
    $estadoFiltro = $request->get('estado', 'all');

    // Primero, obtener las muestras según el filtro
    $queryMuestras = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true);

    // Aplicar filtro según el estado seleccionado
    if ($estadoFiltro !== 'all') {
        if ($estadoFiltro === 'pendientes_coordinar') {
            // Muestras pendientes por coordinar (sin análisis activos)
            $queryMuestras->where('active_ot', false);
        } else {
            // Filtrar por estado de análisis de la muestra
            $queryMuestras->where('cotio_estado_analisis', $estadoFiltro);
        }
    }

    $muestras = $queryMuestras->with(['cotizacion'])
        ->get()
        ->groupBy(fn($m) => $m->cotio_numcoti . '-' . $m->cotio_item . '-' . $m->instance_number);

    // Ahora obtener los análisis relacionados con estas muestras
    $muestrasIds = collect($muestras->keys())->map(function($key) {
        $parts = explode('-', $key);
        return [
            'cotio_numcoti' => $parts[0],
            'cotio_item' => $parts[1],
            'instance_number' => $parts[2]
        ];
    });

    $analisis = collect();
    if ($muestrasIds->isNotEmpty()) {
        $analisis = CotioInstancia::where('cotio_subitem', '>', 0)
            ->where('active_ot', true)
            ->where(function($q) use ($muestrasIds) {
                foreach ($muestrasIds as $muestra) {
                    $q->orWhere(function($subQ) use ($muestra) {
                        $subQ->where('cotio_numcoti', $muestra['cotio_numcoti'])
                             ->where('cotio_item', $muestra['cotio_item'])
                             ->where('instance_number', $muestra['instance_number']);
                    });
                }
            })
            ->with(['cotizacion', 'responsablesAnalisis', 'herramientasLab'])
            ->get();
    }

    // Asignar muestras a cada análisis
    $analisis->each(function($item) use ($muestras) {
        $muestraKey = $item->cotio_numcoti . '-' . $item->cotio_item . '-' . $item->instance_number;
        $muestra = $muestras->get($muestraKey)?->first();
        
        $item->setRelation('muestra', $muestra);
        $item->muestra_instance_number = $muestra?->instance_number;
    });
    
    // Agrupar análisis por muestra
    $analisisAgrupados = $analisis->groupBy(function($item) {
        return $item->cotio_numcoti . '-' . $item->cotio_item . '-' . $item->muestra_instance_number;
    });

    // Agregar muestras que no tienen análisis activos
    foreach ($muestras as $key => $muestraGroup) {
        if (!$analisisAgrupados->has($key)) {
            $muestra = $muestraGroup->first();
            // Crear un análisis ficticio para mantener la estructura
            $analisisFicticio = new CotioInstancia();
            $analisisFicticio->setRelation('muestra', $muestra);
            $analisisFicticio->cotio_numcoti = $muestra->cotio_numcoti;
            $analisisFicticio->cotio_item = $muestra->cotio_item;
            $analisisFicticio->instance_number = $muestra->instance_number;
            $analisisFicticio->muestra_instance_number = $muestra->instance_number;
            $analisisAgrupados->put($key, collect([$analisisFicticio]));
        }
    }

    // Ordenar grupos según prioridad y estado
    $analisisAgrupados = $analisisAgrupados->sortBy(function($grupo, $key) {
        $primerAnalisis = $grupo->first();
        $muestra = $primerAnalisis->muestra;
        
        if (!$muestra) {
            return 9999; // Sin muestra va al final
        }
        
        $estado = $muestra->cotio_estado_analisis ?? 'pendiente_coordinar';
        $esPriori = $muestra->es_priori ?? false;
        
        // Si está analizado, siempre va al final (independientemente de prioridad)
        if ($estado === 'analizado') {
            return 500; // Todas las analizadas al final
        }
        
        // Orden de prioridad (solo para no analizadas)
        if ($esPriori) {
            return 100; // Prioridad va primero
        }
        
        // Orden por estado (solo para no analizadas y no prioritarias)
        switch ($estado) {
            case 'pendiente_coordinar':
            case null:
                return 200; // Pendientes por coordinar - segundo lugar (después de prioritarias)
            case 'en revision analisis':
                return 300; // En revisión (turquesas) - tercer lugar
            case 'coordinado analisis':
                return 400; // Coordinadas (amarillas) - cuarto lugar
            default:
                return 600;
        }
    });
    
    
    // Estadísticas basadas en las muestras (cotio_subitem = 0)
    $pendientesPorCoordinar = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('active_ot', false)
        ->count();
    
    $pendientesDeAnalisis = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'coordinado analisis')
        ->count();
    
    $pendientesDeRevision = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'en revision analisis')
        ->count();
    
    $finalizados = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'analizado')
        ->count();

    $anulados = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('time_annulled', '>', 0)
        ->count();

    // Análisis próximos a vencer (basado en muestras)
    $muestrasProximas = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('fecha_fin_ot', '>=', now())
        ->where('fecha_fin_ot', '<=', now()->addDays(3))
        ->where(function($query) use ($userCodigo) {
            $query->where('coordinador_codigo', $userCodigo)
                ->orWhereHas('responsablesAnalisis', function($q) use ($userCodigo) {
                    $q->where('instancia_responsable_analisis.usu_codigo', $userCodigo);
                });
        })
        ->with(['cotizacion'])
        ->orderBy('fecha_fin_ot')
        ->get();

    // Obtener análisis relacionados con estas muestras próximas
    $analisisProximos = collect();
    if ($muestrasProximas->isNotEmpty()) {
        $analisisProximos = CotioInstancia::where('cotio_subitem', '>', 0)
            ->where('active_ot', true)
            ->where(function($q) use ($muestrasProximas) {
                foreach ($muestrasProximas as $muestra) {
                    $q->orWhere(function($subQ) use ($muestra) {
                        $subQ->where('cotio_numcoti', $muestra->cotio_numcoti)
                             ->where('cotio_item', $muestra->cotio_item)
                             ->where('instance_number', $muestra->instance_number);
                    });
                }
            })
            ->with(['cotizacion', 'responsablesAnalisis'])
            ->get();
    }

    // Asignar muestras a cada análisis próximo
    $analisisProximos->each(function($item) use ($muestrasProximas) {
        $muestra = $muestrasProximas->where('cotio_numcoti', $item->cotio_numcoti)
                                   ->where('cotio_item', $item->cotio_item)
                                   ->where('instance_number', $item->instance_number)
                                   ->first();
        $item->setRelation('muestra', $muestra);
    });

    // Agrupar análisis próximos por muestra
    $analisisProximosAgrupados = $analisisProximos->groupBy(function($item) {
        return $item->cotio_numcoti . '-' . $item->cotio_item . '-' . $item->instance_number;
    });

    // Agregar muestras sin análisis activos a los próximos
    foreach ($muestrasProximas as $muestra) {
        $key = $muestra->cotio_numcoti . '-' . $muestra->cotio_item . '-' . $muestra->instance_number;
        if (!$analisisProximosAgrupados->has($key)) {
            $analisisFicticio = new CotioInstancia();
            $analisisFicticio->setRelation('muestra', $muestra);
            $analisisFicticio->cotio_numcoti = $muestra->cotio_numcoti;
            $analisisFicticio->cotio_item = $muestra->cotio_item;
            $analisisFicticio->instance_number = $muestra->instance_number;
            $analisisFicticio->fecha_fin = $muestra->fecha_fin_ot;
            $analisisProximosAgrupados->put($key, collect([$analisisFicticio]));
        }
    }
    
    // Herramientas de laboratorio en uso
    $herramientasEnUso = InventarioLab::whereHas('cotioInstancias', function($q) {
        $q->where('cotio_instancias.cotio_subitem', '>', 0)
          ->where('cotio_instancias.enable_ot', true)
          ->where('cotio_instancias.cotio_estado', '!=', 'finalizado');
    })->withCount(['cotioInstancias' => function($q) {
        $q->where('cotio_instancias.cotio_subitem', '>', 0)
          ->where('cotio_instancias.enable_ot', true)
          ->where('cotio_instancias.cotio_estado', '!=', 'finalizado');
    }])->get();
    
    return view('dashboard.analisis', compact(
        'analisisAgrupados',
        'pendientesPorCoordinar',
        'pendientesDeAnalisis',
        'pendientesDeRevision',
        'finalizados',
        'anulados',
        'analisisProximosAgrupados',
        'herramientasEnUso',
        'estadoFiltro'
    ));
}

// Método temporal para debuggear los filtros
public function debugAnalisis(Request $request)
{
    $estadoFiltro = $request->get('estado', 'all');
    
    // Debug info
    $debug = [];
    
    // Contar muestras por estado
    $debug['muestras_por_estado'] = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true)
        ->selectRaw('cotio_estado_analisis, active_ot, count(*) as total')
        ->groupBy(['cotio_estado_analisis', 'active_ot'])
        ->get()
        ->toArray();
    
    // Aplicar el filtro actual
    $queryMuestras = CotioInstancia::where('cotio_subitem', 0)
        ->where('enable_ot', true);

    if ($estadoFiltro !== 'all') {
        if ($estadoFiltro === 'pendientes_coordinar') {
            $queryMuestras->where('active_ot', false);
        } else {
            $queryMuestras->where('cotio_estado_analisis', $estadoFiltro);
        }
    }
    
    $debug['filtro_aplicado'] = $estadoFiltro;
    $debug['muestras_filtradas'] = $queryMuestras->count();
    $debug['muestras_filtradas_detalle'] = $queryMuestras->limit(5)->get(['cotio_numcoti', 'cotio_item', 'instance_number', 'cotio_estado_analisis', 'active_ot'])->toArray();
    
    return response()->json($debug);
}


}
