<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use App\Models\InstanciaResponsableMuestreo;
use App\Models\InstanciaResponsableAnalisis;
use Illuminate\Support\Facades\Auth;


class CotioInstancia extends Model
{
    protected $table = 'cotio_instancias';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'cotio_numcoti', 
        'cotio_item', 
        'cotio_subitem', 
        'cotio_descripcion',
        'instance_number',
        'fecha_muestreo', 
        'observaciones',
        'observaciones_medicion_muestreador',
        'observaciones_medicion_coord_muestreo',
        'resultado', 
        'resultado_2',
        'resultado_3',
        'resultado_final',
        // 'observaciones_medicion',
        'completado', 
        'enable_muestreo', 
        'fecha_inicio_muestreo',
        'fecha_fin_muestreo',
        'fecha_inicio_ot', 
        'fecha_fin_ot', 
        'cotio_estado', 
        'cotio_identificacion', 
        'volumen_muestra',
        'vehiculo_asignado',
        'cotio_observaciones_suspension',
        'image',
        'active_ot',
        'latitud',
        'longitud',
        'coordinador_codigo',
        'enable_inform',
        'enable_ot',
        'cotio_estado_analisis',
        'observacion_resultado',
        'observacion_resultado_2',
        'observacion_resultado_3',
        'observacion_resultado_final',
        'responsable_resultado_1',
        'responsable_resultado_2',
        'responsable_resultado_3',
        'responsable_resultado_final',
        'observaciones_ot',
        'fecha_carga_ot'
    ];

    protected $casts = [
        'fecha_muestreo' => 'datetime',
        'completado' => 'boolean',
        'enable_muestreo' => 'boolean',
        'fecha_inicio_muestreo' => 'datetime', 
        'fecha_fin_muestreo' => 'datetime',
        'fecha_inicio_ot' => 'datetime', 
        'fecha_fin_ot' => 'datetime',
        'fecha_carga_ot' => 'datetime',
        'enable_ot' => 'boolean',
    ];

    public function responsablesMuestreo()
    {
        return $this->belongsToMany(
            User::class,
            'instancia_responsable_muestreo',
            'cotio_instancia_id',
            'usu_codigo',
            'id',
            'usu_codigo'
        )->using(InstanciaResponsableMuestreo::class)
          ->withTimestamps()
          ->withPivot(['created_at', 'updated_at']);
    }

    public function responsablesAnalisis()
    {
        return $this->belongsToMany(
            User::class,
            'instancia_responsable_analisis',
            'cotio_instancia_id',
            'usu_codigo',
            'id',
            'usu_codigo'
        )->using(InstanciaResponsableAnalisis::class)
          ->withTimestamps()
          ->withPivot(['created_at', 'updated_at']);
    }

    public function valoresVariables()
    {
        return $this->hasMany(CotioValorVariable::class, 'cotio_instancia_id');
    }

    public function muestraRaw()
    {
        return $this->belongsTo(CotioInstancia::class, 'cotio_numcoti', 'cotio_numcoti')
                   ->where('cotio_item', $this->cotio_item)
                   ->where('cotio_subitem', 0);
    }

    
    
    public function muestra()
    {
        return $this->belongsTo(Cotio::class, 'cotio_numcoti', 'cotio_numcoti')
                   ->where('cotio_item', $this->cotio_item)
                   ->where('cotio_subitem', 0);
    }

    public function gemelos()
    {
        return $this->newQuery()
            ->where('cotio_numcoti', $this->cotio_numcoti)
            ->where('cotio_item', $this->cotio_item)
            ->where('cotio_subitem', $this->cotio_subitem)
            ->where('instance_number', '!=', $this->instance_number)
            ->where('enable_ot', true)
            ->get();
    }

    public function tarea()
    {
        return $this->belongsTo(Cotio::class, 'cotio_numcoti', 'cotio_numcoti')
                   ->where('cotio_item', $this->cotio_item)
                   ->where('cotio_subitem', $this->cotio_subitem);
    }

    public function tareas()
    {
        return $this->hasMany(CotioInstancia::class, 'cotio_numcoti', 'cotio_numcoti')
                    ->where('cotio_item', $this->cotio_item)
                    ->where('instance_number', $this->instance_number)
                    ->where('cotio_subitem', '>', 0); // Fetch analyses
    }

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_codigo', 'usu_codigo');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_asignado');
    }

    public function herramientas(): BelongsToMany
    {
        return $this->belongsToMany(
            InventarioMuestreo::class,
            'cotio_inventario_muestreo',
            'cotio_instancia_id', 
            'inventario_muestreo_id'
        )->withPivot([
            'cantidad', 
            'observaciones',
            'cotio_numcoti',
            'cotio_item',
            'cotio_subitem',
            'instance_number',
        ]);
    }

    public function cotizacion()
    {
        return $this->belongsTo(Coti::class, 'cotio_numcoti', 'coti_num');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url('images/' . $this->image) : null;
    }

    public function herramientasLab()
    {
        return $this->belongsToMany(
            InventarioLab::class,
            'cotio_inventario_lab',
            'cotio_instancia_id',   
            'inventario_lab_id'
        )
        ->withPivot(['cantidad', 'observaciones', 'cotio_numcoti', 'cotio_item', 'cotio_subitem', 'instance_number']);
    }

    public function variablesMuestreo()
    {
        return $this->hasMany(CotioValorVariable::class, 'cotio_instancia_id');
    }


    protected static function booted()
{
        static::updated(function ($instancia) {
            if (($instancia->isDirty('cotio_estado') || $instancia->isDirty('cotio_estado_analisis')) && $instancia->coordinador_codigo) {
                $tipoEstado = $instancia->isDirty('cotio_estado') ? 'muestreo' : 'análisis';
                $nuevoEstado = $instancia->isDirty('cotio_estado') ? 
                    $instancia->cotio_estado : $instancia->cotio_estado_analisis;
                
                $usuario = Auth::user();
                
                SimpleNotification::create([
                    'coordinador_codigo' => $instancia->coordinador_codigo,
                    'sender_codigo' => $usuario->usu_codigo,
                    'instancia_id' => $instancia->id,
                    'mensaje' => sprintf(
                        '%s cambió el estado de %s a "%s" para la muestra "%s" de la cotización %s',
                        $usuario->usu_descripcion,
                        $tipoEstado,
                        $nuevoEstado,
                        $instancia->cotio_descripcion,
                        $instancia->cotio_numcoti
                    )
                ]);
            }
        });
    }


}