<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotioValorVariable extends Model
{
    protected $table = 'cotio_valores_variables';

    protected $fillable = [
        'cotio_instancia_id',
        'variable',
        'valor',
    ];

    public function cotioInstancia()
    {
        return $this->belongsTo(CotioInstancia::class, 'cotio_instancia_id');
    }
}
