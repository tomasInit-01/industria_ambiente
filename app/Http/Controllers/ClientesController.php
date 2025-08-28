<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Clientes;


class ClientesController extends Controller {
    
    public function index()
    {
        $clientes = Clientes::limit(10)->get();
        return View::make('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $cliente = new Clientes();
        $cliente->cli_nombre = $request->cli_nombre;
        $cliente->cli_descripcion = $request->cli_descripcion;
        $cliente->save();
        return redirect()->route('clientes.index');
    }
    
    public function edit($id)
    {
        $cliente = Clientes::find($id);
        return View::make('clientes.edit', compact('cliente'));
    }
    
    public function update(Request $request, $id)
    {
        $cliente = Clientes::find($id);
        $cliente->cli_nombre = $request->cli_nombre;
        $cliente->cli_descripcion = $request->cli_descripcion;
        $cliente->save();
        return redirect()->route('clientes.index');
    }
    
}