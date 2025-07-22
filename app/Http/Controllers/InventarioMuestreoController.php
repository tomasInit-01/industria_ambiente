<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventarioMuestreo;



class InventarioMuestreoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:libre,ocupado',
            'n_serie_lote' => 'nullable|string|max:255',
        ]);
    
        $query = InventarioMuestreo::query();
    
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('equipamiento', 'like', "%{$searchTerm}%")
                  ->orWhere('marca_modelo', 'like', "%{$searchTerm}%");
            });
        }
    
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
    
        if ($request->filled('n_serie_lote')) {
            $serieTerm = $request->input('n_serie_lote');
            $query->where(function($q) use ($serieTerm) {
                $q->where('n_serie_lote', 'like', "%{$serieTerm}%");
            });
        }
    
        $query->orderBy('equipamiento');
    
        $inventarios = $query->paginate(50);
    
        return view('inventarios-muestreo.index', compact('inventarios'));
    }

    

    public function show($id)
    {
        $inventario = InventarioMuestreo::find($id);
        return view('inventarios-muestreo.show', compact('inventario'));
    }

    public function update(Request $request, $id)
    {
        $inventario = InventarioMuestreo::find($id);
        $inventario->equipamiento = $request->equipamiento;
        $inventario->marca_modelo = $request->marca_modelo;
        $inventario->n_serie_lote = $request->n_serie_lote;
        $inventario->observaciones = $request->observaciones;
        $inventario->save();
        return redirect()->route('inventarios-muestreo.index')->with('success', 'Equipamiento actualizado correctamente.');
    }
    
    public function destroy($id)
    {
        $inventario = InventarioMuestreo::find($id);
        $inventario->delete();
        return redirect()->route('inventarios-muestreo.index');
    }


    public function create()
    {
        return view('inventarios-muestreo.create');
    }

    public function store(Request $request)
    {
        $inventario = new InventarioMuestreo();
        $inventario->equipamiento = $request->equipamiento;
        $inventario->marca_modelo = $request->marca_modelo;
        $inventario->n_serie_lote = $request->n_serie_lote;
        $inventario->observaciones = $request->observaciones;
        $inventario->save();
        
        return redirect()->route('inventarios-muestreo.index')->with('success', 'Equipamiento creado correctamente.');
        
    }
    
}