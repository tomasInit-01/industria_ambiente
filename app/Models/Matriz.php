<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matriz extends Model
{
    protected $table = 'matriz';
    
    protected $primaryKey = 'codigo_matriz';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    
    public function cotizaciones()
    {
        return $this->hasMany(Coti::class, 'coti_codigomatriz', 'codigo_matriz');

    }
}