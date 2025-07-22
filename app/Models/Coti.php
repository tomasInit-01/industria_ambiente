<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Matriz;
use App\Models\User;
use App\Models\Cotio;

class Coti extends Model
{
    protected $table = 'coti';
    protected $primaryKey = 'coti_num';
    public $incrementing = false;
    protected $keyType = 'string'; 

    public function tareas()
    {
        return $this->hasMany(Cotio::class, 'cotio_numcoti', 'coti_num');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'coti_responsable');
    }

    public function matriz()
    {
        return $this->belongsTo(Matriz::class, 'coti_codigomatriz', 'matriz_codigo');
    }

    public function instancias()
    {
        return $this->hasMany(CotioInstancia::class, 'cotio_numcoti', 'coti_num');
    }

    public function categoriasHabilitadas()
    {
        return $this->hasMany(Cotio::class, 'cotio_numcoti', 'coti_num')
            ->where('cotio_subitem', 0)
            ->where('enable_ot', true);
    }

    public function tareasDeCategoriasHabilitadas()
    {
        return $this->hasMany(Cotio::class, 'cotio_numcoti', 'coti_num')
            ->where('cotio_subitem', '!=', 0)
            ->whereIn('cotio_item', function($query) {
                $query->select('cotio_item')
                    ->from('cotio')
                    ->whereColumn('cotio_numcoti', 'coti.coti_num')
                    ->where('cotio_subitem', 0)
                    ->where('enable_ot', true);
            });
    }
}
