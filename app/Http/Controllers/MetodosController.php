<?php

namespace App\Http\Controllers;

use App\Models\Metodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MetodosController extends Controller
{
    public function __construct()
    {
  
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('q');
        $query = Metodo::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('metodo_codigo', 'like', "%{$search}%")
                  ->orWhere('metodo_descripcion', 'like', "%{$search}%");
            });
        }

        //descendente
        $metodos = $query->orderByRaw("CAST(TRIM(metodo_codigo) AS INTEGER) DESC")->paginate(15)->withQueryString();

        return view('metodos.index', compact('metodos', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ultimo = Metodo::select('metodo_codigo')
            ->orderByRaw("CAST(TRIM(metodo_codigo) AS INTEGER) DESC")
            ->first();

        $ultimoNumero = $ultimo ? (int)trim($ultimo->metodo_codigo) : 0;
        $siguiente = str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);

        return view('metodos.create', compact('siguiente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'metodo_descripcion' => ['required', 'string', 'max:255'],
        ]);

        $ultimo = Metodo::select('metodo_codigo')
            ->orderByRaw("CAST(TRIM(metodo_codigo) AS INTEGER) DESC")
            ->first();
        $ultimoNumero = $ultimo ? (int)trim($ultimo->metodo_codigo) : 0;
        $metodo_codigo = str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);

        Metodo::create([
            'metodo_codigo' => $metodo_codigo,
            'metodo_descripcion' => $validated['metodo_descripcion'],
        ]);

        return redirect()->route('metodos.index')
            ->with('success', 'Método creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($metodo_codigo)
    {
        $metodo = Metodo::where('metodo_codigo', $metodo_codigo)->firstOrFail();
        return redirect()->route('metodos.edit', trim($metodo->metodo_codigo));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($metodo_codigo)
    {
        $metodo = Metodo::where('metodo_codigo', $metodo_codigo)->firstOrFail();
        return view('metodos.edit', compact('metodo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $metodo_codigo)
    {
        $metodo = Metodo::where('metodo_codigo', $metodo_codigo)->firstOrFail();

        $validated = $request->validate([
            'metodo_descripcion' => ['required', 'string', 'max:255'],
        ]);

        $metodo->update([
            'metodo_descripcion' => $validated['metodo_descripcion'],
        ]);

        return redirect()->route('metodos.index')
            ->with('success', 'Método actualizado correctamente.');
    }
    /**
     * Show the form for confirming deletion.
     */
    public function delete($metodo_codigo)
    {
        $metodo = Metodo::where('metodo_codigo', $metodo_codigo)->firstOrFail();
        // La ruta definida es DELETE, realizamos la eliminación directa
        $metodo->delete();  
        return redirect()->route('metodos.index')
            ->with('success', 'Método eliminado correctamente.');
    }
}
