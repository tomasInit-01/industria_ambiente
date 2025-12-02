<?php

namespace App\Http\Controllers;

use App\Models\MetodoMuestreo;
use App\Models\MetodoAnalisis;
use App\Models\LeyNormativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Estadísticas generales
        $estadisticas = [
            'metodos_muestreo' => MetodoMuestreo::count(),
            'metodos_muestreo_activos' => MetodoMuestreo::where('activo', true)->count(),
            'metodos_analisis' => MetodoAnalisis::count(),
            'metodos_analisis_activos' => MetodoAnalisis::where('activo', true)->count(),
            'leyes_normativas' => LeyNormativa::count(),
            'leyes_normativas_activas' => LeyNormativa::where('activo', true)->count(),
        ];

        // Elementos recientes (últimos 5)
        $metodosMuestreoRecientes = MetodoMuestreo::latest()->take(3)->get();
        $metodosAnalisisRecientes = MetodoAnalisis::latest()->take(3)->get();
        $normativasRecientes = LeyNormativa::latest()->take(5)->get();

        // Combinar métodos recientes
        $metodosRecientes = $metodosMuestreoRecientes->concat($metodosAnalisisRecientes)
                                                   ->sortByDesc('created_at')
                                                   ->take(5);

        $recientes = [
            'metodos' => $metodosRecientes,
            'normativas' => $normativasRecientes
        ];

        // Elementos más usados (con conteo de cotios)
        $metodosMuestreoMasUsados = MetodoMuestreo::withCount('cotios')
                                                 ->having('cotios_count', '>', 0)
                                                 ->orderByDesc('cotios_count')
                                                 ->take(5)
                                                 ->get();

        $metodosAnalisisMasUsados = MetodoAnalisis::withCount('cotios')
                                                 ->having('cotios_count', '>', 0)
                                                 ->orderByDesc('cotios_count')
                                                 ->take(5)
                                                 ->get();

        $masUsados = [
            'metodos_muestreo' => $metodosMuestreoMasUsados,
            'metodos_analisis' => $metodosAnalisisMasUsados
        ];

        return view('admin.dashboard', compact('estadisticas', 'recientes', 'masUsados'));
    }
}
