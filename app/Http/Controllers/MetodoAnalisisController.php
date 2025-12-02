<?php

namespace App\Http\Controllers;

use App\Models\MetodoAnalisis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetodoAnalisisController extends Controller
{
    public function __construct()
    {
     
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MetodoAnalisis::query();

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->buscar($request->search);
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        // Filtro por calibración requerida
        if ($request->filled('requiere_calibracion')) {
            $query->where('requiere_calibracion', $request->requiere_calibracion);
        }

        $metodos = $query->orderBy('codigo')->paginate(15);

        return view('metodos-analisis.index', compact('metodos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('metodos-analisis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:metodos_analisis,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'equipo_requerido' => 'nullable|string|max:255',
            'procedimiento' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:255',
            'limite_deteccion_default' => 'nullable|numeric|min:0',
            'limite_cuantificacion_default' => 'nullable|numeric|min:0',
            'costo_base' => 'nullable|numeric|min:0',
            'tiempo_estimado_horas' => 'nullable|integer|min:1',
            'requiere_calibracion' => 'boolean',
            'activo' => 'boolean'
        ]);

        $validated['requiere_calibracion'] = $request->has('requiere_calibracion');
        $validated['activo'] = $request->has('activo');

        MetodoAnalisis::create($validated);

        return redirect()->route('metodos-analisis.index')
                        ->with('success', 'Método de análisis creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoAnalisis $metodoAnalisis)
    {
        $metodoAnalisis->load('cotios');
        return view('metodos-analisis.show', compact('metodoAnalisis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MetodoAnalisis $metodoAnalisis)
    {
        return view('metodos-analisis.edit', compact('metodoAnalisis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodoAnalisis $metodoAnalisis)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:metodos_analisis,codigo,' . $metodoAnalisis->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'equipo_requerido' => 'nullable|string|max:255',
            'procedimiento' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:255',
            'limite_deteccion_default' => 'nullable|numeric|min:0',
            'limite_cuantificacion_default' => 'nullable|numeric|min:0',
            'costo_base' => 'nullable|numeric|min:0',
            'tiempo_estimado_horas' => 'nullable|integer|min:1',
            'requiere_calibracion' => 'boolean',
            'activo' => 'boolean'
        ]);

        $validated['requiere_calibracion'] = $request->has('requiere_calibracion');
        $validated['activo'] = $request->has('activo');

        $metodoAnalisis->update($validated);

        return redirect()->route('metodos-analisis.index')
                        ->with('success', 'Método de análisis actualizado exitosamente.');
    }

    /**
     * Show the form for confirming deletion.
     */
    public function delete(MetodoAnalisis $metodoAnalisis)
    {
        $metodoAnalisis->load('cotios');
        return view('metodos-analisis.delete', compact('metodoAnalisis'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoAnalisis $metodoAnalisis)
    {
        // Verificar si está siendo usado
        if ($metodoAnalisis->cotios()->count() > 0) {
            return redirect()->route('metodos-analisis.index')
                            ->with('error', 'No se puede eliminar el método porque está siendo usado en cotizaciones.');
        }

        $metodoAnalisis->delete();

        return redirect()->route('metodos-analisis.index')
                        ->with('success', 'Método de análisis eliminado exitosamente.');
    }
}
