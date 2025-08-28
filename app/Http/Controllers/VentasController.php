<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Ventas;


class VentasController extends Controller {
    public function index()
    {
        $cotizaciones = Ventas::limit(10)->get();
        // dd($cotizaciones);

        return View::make('ventas.index', compact('cotizaciones'));
    }

    public function create() 
    {
        return View::make('ventas.create');
    }

    public function store(Request $request)
    {
        $cotizacion = new Ventas();
        $cotizacion->coti_nombre = $request->coti_nombre;
        $cotizacion->coti_descripcion = $request->coti_descripcion;
        $cotizacion->save();
        return redirect()->route('ventas.index');
    }

    public function edit($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        $cotizacion = Ventas::find($id);
        if (!$cotizacion) {
            abort(404, 'Cotización not found');
        }
        return View::make('ventas.edit', compact('cotizacion'));
    }
    
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        $cotizacion = Ventas::find($id);
        if (!$cotizacion) {
            abort(404, 'Cotización not found');
        }
        $cotizacion->delete();
        return redirect()->route('ventas.index');
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Ventas::find($id);
        $cotizacion->coti_nombre = $request->coti_nombre;
        $cotizacion->coti_descripcion = $request->coti_descripcion;
        $cotizacion->save();
        return redirect()->route('ventas.index');
    }
    
}