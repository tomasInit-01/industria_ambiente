<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventarioLab;



class InventarioLabController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:libre,ocupado',
            'n_serie_lote' => 'nullable|string|max:255',
        ]);
    
        $query = InventarioLab::query();
    
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('equipamiento', 'like', "%{$searchTerm}%")
                  ->orWhere('marca_modelo', 'like', "%{$searchTerm}%");
            });
        }
    
        if ($request->filled('activo')) {
            $query->where('activo', $request->input('activo'));
        }
    
        if ($request->filled('n_serie_lote')) {
            $serieTerm = $request->input('n_serie_lote');
            $query->where(function($q) use ($serieTerm) {
                $q->where('n_serie_lote', 'like', "%{$serieTerm}%")
                  ->orWhere('codigo_ficha', 'like', "%{$serieTerm}%");
            });
        }
    
        $query->orderBy('equipamiento');
    
        $inventarios = $query->paginate(50);
    
        return view('inventarios.index', compact('inventarios'));
    }

    

    public function show($id)
    {
        $inventario = InventarioLab::find($id);
        return view('inventarios.show', compact('inventario'));
    }

    public function update(Request $request, $id)
    {
        $inventario = InventarioLab::find($id);
        $inventario->equipamiento = $request->equipamiento;
        $inventario->marca_modelo = $request->marca_modelo;
        $inventario->n_serie_lote = $request->n_serie_lote;
        $inventario->codigo_ficha = $request->codigo_ficha;
        $inventario->observaciones = $request->observaciones;
        $inventario->activo = $request->activo;
        $inventario->fecha_calibracion = $request->fecha_calibracion;
        $inventario->save();
        return redirect()->route('inventarios.index')->with('success', 'Equipamiento actualizado correctamente.');
    }
    
    public function destroy($id)
    {
        $inventario = InventarioLab::find($id);
        $inventario->delete();
        return redirect()->route('inventarios.index');
    }


    public function create()
    {
        return view('inventarios.create');
    }

    public function store(Request $request)
    {
        $inventario = new InventarioLab();
        $inventario->equipamiento = $request->equipamiento;
        $inventario->marca_modelo = $request->marca_modelo;
        $inventario->n_serie_lote = $request->n_serie_lote;
        $inventario->codigo_ficha = $request->codigo_ficha;
        $inventario->observaciones = $request->observaciones;
        $inventario->activo = $request->activo;
        $inventario->fecha_calibracion = $request->fecha_calibracion;
        $inventario->save();
        return redirect()->route('inventarios.index')->with('success', 'Equipamiento creado correctamente.');
    }
    
}