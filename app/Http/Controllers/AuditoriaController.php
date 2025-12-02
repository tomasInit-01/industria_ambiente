<?php
// app/Http/Controllers/AuditoriaController.php

namespace App\Http\Controllers;

use App\Models\CotioInstancia;
use App\Models\Coti;
use App\Models\Matriz;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteAuditoriaExport;

class AuditoriaController extends Controller
{
    public function exportar(Request $request)
    {
        try {
            $request->validate([
                'fecha_desde' => 'nullable|date',
                'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde'
            ]);

            $fechaDesde = $request->fecha_desde;
            $fechaHasta = $request->fecha_hasta;

            return Excel::download(
                new ReporteAuditoriaExport($fechaDesde, $fechaHasta), 
                'reporte_auditoria_' . now()->format('Y_m_d_H_i') . '.xlsx'
            );

        } catch (\Exception $e) {
            \Log::error('Error al exportar reporte: ' . $e->getMessage());
            return back()->with('error', 'Error al exportar el reporte: ' . $e->getMessage());
        }
    }

    // Método para debug (opcional)
    public function preview(Request $request)
    {
        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;

        $query = CotioInstancia::with(['coti.matriz']);

        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        $instancias = $query->limit(5)->get();

        $data = [];
        foreach ($instancias as $instancia) {
            $data[] = [
                'empresa' => $instancia->coti->coti_empresa ?? 'No encontrada',
                'id' => $instancia->id,
                'cotizacion' => $instancia->cotio_numcoti,
                'matriz' => $instancia->coti->matriz->matriz_descripcion ?? 'No encontrada',
                'fecha_ingreso_lab' => $instancia->fecha_inicio_ot,
                'fecha_muestreo' => $instancia->fecha_inicio_muestreo,
                'precio' => $instancia->monto,
                'observaciones' => $instancia->observacion_resultado_final
            ];
        }

        return response()->json($data);
    }

    public function index()
    {
        return view('auditoria.index');
    }
}