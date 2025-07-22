<?php

namespace App\Observers;

use App\Models\CotioValorVariable;
use App\Models\CotioHistorialCambios;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class CotioValorVariableObserver
{
    protected $campos = [
        'variable',
        'valor',
        'cotio_instancia_id',
    ];

    /**
     * Handle the CotioValorVariable "created" event.
     */
    public function created(CotioValorVariable $cotioValorVariable): void
    {
        // Registrar creación para campos que no sean null
        foreach ($this->campos as $campo) {
            if (!is_null($cotioValorVariable->$campo)) {
                CotioHistorialCambios::create([
                    'tabla_afectada' => 'cotio_valores_variables',
                    'registro_id' => $cotioValorVariable->id,
                    'campo_modificado' => $campo,
                    'valor_anterior' => null,
                    'valor_nuevo' => $cotioValorVariable->$campo,
                    'usuario_id' => Auth::id() ?? null,
                    'fecha_cambio' => now(),
                    'ip_origen' => Request::ip(),
                    'accion' => 'create',
                ]);
            }
        }
    }

    /**
     * Handle the CotioValorVariable "updated" event.
     */
    public function updated(CotioValorVariable $cotioValorVariable): void
    {
        foreach ($this->campos as $campo) {
            if ($cotioValorVariable->isDirty($campo)) {
                CotioHistorialCambios::create([
                    'tabla_afectada' => 'cotio_valores_variables',
                    'registro_id' => $cotioValorVariable->id,
                    'campo_modificado' => $campo,
                    'valor_anterior' => $cotioValorVariable->getOriginal($campo),
                    'valor_nuevo' => $cotioValorVariable->$campo,
                    'usuario_id' => Auth::id() ?? null,
                    'fecha_cambio' => now(),
                    'ip_origen' => Request::ip(),
                    'accion' => 'update',
                ]);
            }
        }
    }

    /**
     * Handle the CotioValorVariable "deleted" event.
     */
    public function deleted(CotioValorVariable $cotioValorVariable): void
    {
        // Registrar eliminación para campos que no sean null
        foreach ($this->campos as $campo) {
            if (!is_null($cotioValorVariable->$campo)) {
                CotioHistorialCambios::create([
                    'tabla_afectada' => 'cotio_valores_variables',
                    'registro_id' => $cotioValorVariable->id,
                    'campo_modificado' => $campo,
                    'valor_anterior' => $cotioValorVariable->$campo,
                    'valor_nuevo' => null,
                    'usuario_id' => Auth::id() ?? null,
                    'fecha_cambio' => now(),
                    'ip_origen' => Request::ip(),
                    'accion' => 'delete',
                ]);
            }
        }
    }

    /**
     * Handle the CotioValorVariable "restored" event.
     */
    public function restored(CotioValorVariable $cotioValorVariable): void
    {
        //
    }

    /**
     * Handle the CotioValorVariable "force deleted" event.
     */
    public function forceDeleted(CotioValorVariable $cotioValorVariable): void
    {
        //
    }
}
