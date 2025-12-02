<?php

namespace App\Http\Controllers;

use App\Models\Variable;
use Illuminate\Http\Request;

class VariableController extends Controller
{
    public function __construct()
    {
      
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Variable::query();

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->buscar($request->search);
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        // Filtro por tipo
        if ($request->filled('tipo_variable')) {
            $query->porTipo($request->tipo_variable);
        }

        $variables = $query->orderBy('codigo')->paginate(15);
        $tipos = Variable::getTiposUnicos();

        return view('variables.index', compact('variables', 'tipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipos = Variable::getTiposUnicos();
        return view('variables.create', compact('tipos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:variables,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:50',
            'tipo_variable' => 'required|string|max:100',
            'metodo_determinacion' => 'nullable|string',
            'limite_minimo' => 'nullable|numeric',
            'limite_maximo' => 'nullable|numeric|gte:limite_minimo',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->has('activo');

        Variable::create($validated);

        return redirect()->route('variables.index')
                        ->with('success', 'Variable creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Variable $variable)
    {
        $variable->load(['leyesNormativas', 'cotios']);
        return view('variables.show', compact('variable'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variable $variable)
    {
        $tipos = Variable::getTiposUnicos();
        return view('variables.edit', compact('variable', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Variable $variable)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:variables,codigo,' . $variable->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:50',
            'tipo_variable' => 'required|string|max:100',
            'metodo_determinacion' => 'nullable|string',
            'limite_minimo' => 'nullable|numeric',
            'limite_maximo' => 'nullable|numeric|gte:limite_minimo',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->has('activo');

        $variable->update($validated);

        return redirect()->route('variables.index')
                        ->with('success', 'Variable actualizada exitosamente.');
    }

    /**
     * Show the form for confirming deletion.
     */
    public function delete(Variable $variable)
    {
        $variable->load(['leyesNormativas', 'cotios']);
        return view('variables.delete', compact('variable'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variable $variable)
    {
        // Verificar si está siendo usado
        if ($variable->leyesNormativas()->count() > 0 || $variable->cotios()->count() > 0) {
            return redirect()->route('variables.index')
                            ->with('error', 'No se puede eliminar la variable porque está siendo usada.');
        }

        $variable->delete();

        return redirect()->route('variables.index')
                        ->with('success', 'Variable eliminada exitosamente.');
    }

    /**
     * API endpoint para obtener todas las variables activas
     */
    public function apiIndex()
    {
        $variables = Variable::activas()
                           ->select('id', 'codigo', 'nombre', 'tipo_variable', 'unidad_medicion')
                           ->orderBy('codigo')
                           ->get();
        
        return response()->json($variables);
    }

    /**
     * API endpoint para crear una variable vía AJAX
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:variables,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'unidad_medicion' => 'nullable|string|max:50',
            'tipo_variable' => 'required|string|max:100',
        ]);

        $validated['activo'] = true;

        $variable = Variable::create($validated);

        return response()->json([
            'id' => $variable->id,
            'codigo' => $variable->codigo,
            'nombre' => $variable->nombre,
            'tipo_variable' => $variable->tipo_variable,
            'unidad_medicion' => $variable->unidad_medicion
        ]);
    }
}
