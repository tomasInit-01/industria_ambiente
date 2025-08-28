<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    protected $table = 'coti';
    protected $primaryKey = 'coti_num';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'coti_num',
        'coti_descripcion',
        'coti_codigocli',
        'coti_fechaalta',
        'coti_fechaaprobado',
        'coti_aprobo',
        'coti_estado',
        'coti_codigomatriz',
        'coti_responsable',
        'coti_fechafin',
        'coti_notas',
        'coti_fechaencurso',
        'coti_fechaaltatecnica',
        'coti_empresa',
        'coti_establecimiento',
        'coti_contacto',
        'coti_direccioncli',
        'coti_localidad',
        'coti_partido',
        'coti_cuit',
        'coti_codigopostal',
        'coti_telefono',
        'coti_solensayo',
        'coti_remito',
        'coti_importe',
        'coti_sector',
        'coti_codigopag',
        'coti_usos',
        'coti_codigosuc',
        'coti_codigodiv',
        'coti_paridad',
        'coti_codigolp',
        'coti_nroprecio',
        'coti_vigencia',
        'coti_factor',
        'coti_interes',
        'coti_iva',
        'coti_impint',
        'coti_perciva',
        'coti_iibb',
        'coti_ganancias',
        'coti_acre',
        'coti_dto1',
        'coti_dto2',
        'coti_mail1',
        'coti_mail2',
        'coti_mail3',
        'coti_id',
        'coti_nrooc',
        'coti_abono',
        'coti_codigoclif',
        'coti_codigosucf'
    ];
}