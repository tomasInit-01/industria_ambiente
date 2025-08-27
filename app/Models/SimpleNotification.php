<?php

// app/Models/SimpleNotification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleNotification extends Model
{
    protected $fillable = [
        'coordinador_codigo', // receptor
        'sender_codigo',       // emisor (nuevo campo)
        'instancia_id',
        'mensaje',
        'url',
        'leida'
    ];

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_codigo', 'usu_codigo');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_codigo', 'usu_codigo');
    }

    public function instancia()
    {
        return $this->belongsTo(CotioInstancia::class, 'instancia_id');
    }

    /**
     * Genera la URL correspondiente según el rol del destinatario
     */
    public static function generarUrlPorRol($coordinadorCodigo, $instanciaId = null)
    {
        $coordinador = User::where('usu_codigo', $coordinadorCodigo)->first();
        
        if (!$coordinador || !$instanciaId) {
            return null;
        }
    
        $instancia = CotioInstancia::find($instanciaId);
        if (!$instancia) {
            return null;
        }
    
        switch ($coordinador->rol) {
            case 'coordinador_lab':
                return route('categoria.verOrden', [
                    'cotizacion' => $instancia->cotio_numcoti,
                    'item' => $instancia->cotio_item,
                    'instance' => $instancia->instance_number
                ]);
                
            case 'laboratorio':
                return route('ordenes.all.show', [
                    $instancia->cotio_numcoti,
                    $instancia->cotio_item,
                    $instancia->cotio_subitem,
                    $instancia->instance_number
                ]);
                
            case 'coordinador_muestreo':
                return route('categoria.verMuestra', [
                    'cotizacion' => $instancia->cotio_numcoti,
                    'item' => $instancia->cotio_item,
                    'instance' => $instancia->instance_number
                ]);
                
            case 'muestreador':
                return route('tareas.all.show', [
                    $instancia->cotio_numcoti,
                    $instancia->cotio_item,
                    $instancia->cotio_subitem,
                    $instancia->instance_number
                ]);
                
            default:
                return null;
        }
    }
}