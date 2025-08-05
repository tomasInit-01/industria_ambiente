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

    // Base query para análisis
    $query = CotioInstancia::where('cotio_subitem', '=', 0)
        ->where('enable_ot', true);
    // dd($query->get());

    // Aplicar filtro de estado si no es 'all'
    if ($estadoFiltro !== 'all') {
        $query->where('cotio_estado_analisis', $estadoFiltro);
    }

    // Obtener todos los análisis sin paginación
    $analisis = CotioInstancia::where('cotio_subitem', '>', 0)
        ->where('active_ot', true)
        ->when($estadoFiltro !== 'all', function ($q) use ($estadoFiltro) {
            $q->where('cotio_estado_analisis', $estadoFiltro);
        })
        ->with(['cotizacion', 'responsablesAnalisis', 'herramientasLab'])
        ->orderBy('cotio_numcoti')
        ->orderBy('cotio_item')
        ->orderBy('instance_number')
        ->get();



    // Cargar muestras manualmente para los análisis
    $muestrasIds = $analisis->pluck('cotio_numcoti')->unique();
    $muestras = CotioInstancia::whereIn('cotio_numcoti', $muestrasIds)
    ->where('cotio_subitem', 0)
    ->get()
    ->groupBy(fn($m) => $m->cotio_numcoti . '-' . $m->cotio_item . '-' . $m->instance_number);


    // Asignar muestras a cada análisis
    $analisis->each(function($item) use ($muestras) {
        $muestraKey = $item->cotio_numcoti . '-' . $item->cotio_item . '-' . $item->instance_number;
        $muestra = $muestras->get($muestraKey)?->first();
        
        $item->setRelation('muestra', $muestra);
        $item->muestra_instance_number = $muestra?->instance_number;
    });
    
    
    $analisisAgrupados = $analisis->groupBy(function($item) {
        return $item->cotio_numcoti . '-' . $item->cotio_item . '-' . $item->muestra_instance_number;
    });
    
    
    $totalAnalisis = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('enable_ot', true)
        ->count();
    
    $pendientes = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'coordinado analisis')
        ->count();
    
    $suspendidos = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'suspension')
        ->count();
    
    $enProceso = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'en revision analisis')
        ->count();
    
    $finalizados = CotioInstancia::where('cotio_instancias.cotio_subitem', 0)
        ->where('enable_ot', true)
        ->where('cotio_estado_analisis', 'analizado')
        ->count();
    
    // Análisis próximos a vencer
    $analisisProximos = CotioInstancia::where('cotio_subitem', '>', 0)
        ->where('enable_ot', true)
        ->where('fecha_fin_ot', '>=', now())
        ->where('fecha_fin_ot', '<=', now()->addDays(3))
        ->where(function($query) use ($userCodigo) {
            $query->where('coordinador_codigo', $userCodigo)
                ->orWhereHas('responsablesAnalisis', function($q) use ($userCodigo) {
                    $q->where('instancia_responsable_analisis.usu_codigo', $userCodigo);
                });
        })
        ->with(['cotizacion', 'responsablesAnalisis'])
        ->orderBy('cotio_numcoti')
        ->orderBy('cotio_item')
        ->orderBy('instance_number')
        ->get();

    // Cargar muestras para análisis próximos
    $muestrasProximasIds = $analisisProximos->pluck('cotio_numcoti')->unique();
    $muestrasProximas = CotioInstancia::whereIn('cotio_numcoti', $muestrasProximasIds)
        ->where('cotio_subitem', 0)
        ->get()
        ->groupBy(['cotio_numcoti', 'cotio_item']);

    // Asignar muestras a cada análisis próximo
    $analisisProximos->each(function($item) use ($muestrasProximas) {
        $item->setRelation('muestra', 
            $muestrasProximas->get($item->cotio_numcoti)?->get($item->cotio_item)?->first()
        );
    });

    // Agrupar análisis próximos por muestra
    $analisisProximosAgrupados = $analisisProximos->groupBy(function($item) {
        return $item->cotio_numcoti . '-' . $item->cotio_item;
    });
    
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
        'totalAnalisis',
        'pendientes',
        'suspendidos',
        'enProceso',
        'finalizados',
        'analisisProximosAgrupados',
        'herramientasEnUso',
        'estadoFiltro'
    ));
}


}
