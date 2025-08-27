<?php

// app/Http/Controllers/SimpleNotificationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SimpleNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SimpleNotificationController extends Controller
{


    public function marcarLeidas(Request $request)
    {
        // Log::info('metodo marcarLeidas');
        try {
            $notificaciones = SimpleNotification::where('coordinador_codigo', Auth::user()->usu_codigo)
            ->where('leida', false)
            ->get();

        foreach ($notificaciones as $notificacion) {
            $notificacion->update(['leida' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones marcadas como leídas'
        ]);
        } catch (\Throwable $e) {
            Log::error('Error al marcar las notificaciones como leídas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar las notificaciones como leídas'
            ]);
        }
    }

    public function index()
    {
        $notificaciones = SimpleNotification::where('coordinador_codigo', Auth::user()->usu_codigo)
            ->where(function($query) {
                $query->whereNull('sender_codigo') // Notificaciones sin emisor definido
                      ->orWhere('sender_codigo', '!=', Auth::user()->usu_codigo); // O donde el emisor no es el usuario actual
            })
            ->orderBy('created_at', 'desc')
            ->with('instancia')
            ->paginate(10);
            
        return view('notificaciones.index', compact('notificaciones'));
    }
    public function marcarComoLeida($id)
    {
        $notificacion = SimpleNotification::findOrFail($id);
        $notificacion->update(['leida' => true]);
        
        return back()->with('success', 'Notificación marcada como leída');
    }

    public function marcarTodasComoLeidas()
    {
        SimpleNotification::where('coordinador_codigo', Auth::user()->usu_codigo)
            ->where('leida', false)
            ->update(['leida' => true]);
            
        return back()->with('success', 'Todas las notificaciones marcadas como leídas');
    }


}