<?php

namespace App\Http\Controllers;

use App\Models\MetodoMuestreo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetodoMuestreoController extends Controller
{
    public function __construct()
    {
  
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MetodoMuestreo::query();

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->buscar($request->search);
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        $metodos = $query->orderBy('codigo')->paginate(15);

        return view('metodos-muestreo.index', compact('metodos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('metodos-muestreo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:metodos_muestreo,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'equipo_requerido' => 'nullable|string|max:255',
            'procedimiento' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:255',
            'costo_base' => 'nullable|numeric|min:0',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->has('activo');

        MetodoMuestreo::create($validated);

        return redirect()->route('metodos-muestreo.index')
                        ->with('success', 'Método de muestreo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoMuestreo $metodoMuestreo)
    {
        $metodoMuestreo->load('cotios');
        return view('metodos-muestreo.show', compact('metodoMuestreo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MetodoMuestreo $metodoMuestreo)
    {
        return view('metodos-muestreo.edit', compact('metodoMuestreo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodoMuestreo $metodoMuestreo)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:metodos_muestreo,codigo,' . $metodoMuestreo->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'equipo_requerido' => 'nullable|string|max:255',
            'procedimiento' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:255',
            'costo_base' => 'nullable|numeric|min:0',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->has('activo');

        $metodoMuestreo->update($validated);

        return redirect()->route('metodos-muestreo.index')
                        ->with('success', 'Método de muestreo actualizado exitosamente.');
    }

    /**
     * Show the form for confirming deletion.
     */
    public function delete(MetodoMuestreo $metodoMuestreo)
    {
        $metodoMuestreo->load('cotios');
        return view('metodos-muestreo.delete', compact('metodoMuestreo'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoMuestreo $metodoMuestreo)
    {
        // Verificar si está siendo usado
        if ($metodoMuestreo->cotios()->count() > 0) {
            return redirect()->route('metodos-muestreo.index')
                            ->with('error', 'No se puede eliminar el método porque está siendo usado en cotizaciones.');
        }

        $metodoMuestreo->delete();

        return redirect()->route('metodos-muestreo.index')
                        ->with('success', 'Método de muestreo eliminado exitosamente.');
    }
}
