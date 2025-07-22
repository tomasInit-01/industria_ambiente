<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\InventarioLab;
use App\Models\CotioResponsable;
use Illuminate\Support\Facades\Auth;
use App\Models\CotioInstancia;

class Cotio extends Model
{

    protected $table = 'cotio';
    protected $primaryKey = ['cotio_numcoti', 'cotio_item', 'cotio_subitem'];
    public $incrementing = false;
    protected $fillable = [
        'cotio_numcoti',
        'cotio_item',
        'cotio_subitem',
        'cotio_responsable_codigo',
        'vehiculo_asignado',
        'fecha_inicio',
        'fecha_fin',
        'enable_ot',
        'modulo_origen',
        'enable_muestreo',
        'cotio_estado',
        'resultado',
        'active_ot',
        'cotio_identificacion'
    ];
    public $timestamps = false;
    
    
    public function instancias()
    {
        return $this->hasMany(CotioInstancia::class, 'cotio_numcoti', 'cotio_numcoti')
                    ->whereColumn('cotio_item', 'cotio_item')
                    ->whereColumn('cotio_subitem', 'cotio_subitem');
    }

    public function getInstance($instanceNumber)
    {
        return $this->instancias()->where('instance_number', $instanceNumber)->first();
    }

    public function createInstance($instanceNumber)
    {
        return $this->instancias()->create([
            'instance_number' => $instanceNumber,
            'responsable_muestreo' => Auth::user()->usu_codigo
        ]);
    }

    public function getOrCreateInstance($instanceNumber)
    {
        return $this->instancias()->firstOrCreate(
            ['instance_number' => $instanceNumber],
            ['responsable_muestreo' => Auth::user()->usu_codigo]
        );
    }

    

    public function responsablesManual()
    {
        $responsables = CotioResponsable::where('cotio_numcoti', $this->cotio_numcoti)
            ->where('cotio_item', $this->cotio_item)
            ->where('cotio_subitem', $this->cotio_subitem)
            ->get();
    
        $responsables->load('usuario');
        

        return $responsables->map(function ($item) {
            return $item->usuario;
        })->filter()->values(); 
    }
    
    


    public function herramientas()
    {
        return $this->belongsToMany(
            InventarioLab::class,
            'cotio_inventario_lab',
            'cotio_numcoti', 
            'inventario_lab_id'
        )
        ->wherePivot('cotio_item', $this->cotio_item)
        ->wherePivot('cotio_subitem', $this->cotio_subitem)
        ->withPivot('cantidad', 'observaciones');
    }



    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_asignado');
    }


    public function cotizacion()
    {
        return $this->belongsTo(Coti::class, 'cotio_numcoti');
    }

    



    public function responsable()
    {
        return $this->belongsTo(User::class, 'cotio_responsable_codigo', 'usu_codigo');
    }


    public function getIsAsignadaAttribute()
    {
        return !is_null($this->cotio_responsable_codigo);
    }




    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }





   public static function actualizarEstadoCategoria($cotio_numcoti, $cotio_item)
{
    $categoria = self::where([
        'cotio_numcoti' => $cotio_numcoti,
        'cotio_item' => $cotio_item,
        'cotio_subitem' => 0
    ])->first();

    if (!$categoria) {
        return;
    }

    $tareas = self::where('cotio_numcoti', $cotio_numcoti)
        ->where('cotio_item', $cotio_item)
        ->where('cotio_subitem', '>', 0)
        ->where('active_muestreo', true)
        ->get();

    if ($tareas->isEmpty()) {
        return;
    }

    $todosFinalizados = $tareas->every(function ($tarea) {
        return strtolower($tarea->cotio_estado) === 'finalizado';
    });

    $todosPendientes = $tareas->every(function ($tarea) {
        return strtolower($tarea->cotio_estado) === 'pendiente';
    });

    if ($todosFinalizados) {
        $categoria->cotio_estado = 'finalizado';
        $vehiculo = Vehiculo::find($categoria->vehiculo_asignado);
        if ($vehiculo) {
            $vehiculo->estado = 'libre';
            $vehiculo->save();
        }
        $categoria->vehiculo_asignado = null;
    } elseif ($todosPendientes) {
        $categoria->cotio_estado = 'pendiente';
    } else {
        $categoria->cotio_estado = 'en proceso';
    }

    $categoria->save();
}
    
}
