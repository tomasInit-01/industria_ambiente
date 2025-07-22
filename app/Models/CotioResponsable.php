<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotioResponsable extends Model
{
    protected $table = 'cotio_user';
    public $timestamps = false;

    protected $fillable = [
        'cotio_numcoti',
        'cotio_item',
        'cotio_subitem',
        'usu_codigo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usu_codigo', 'usu_codigo');
    }
}
