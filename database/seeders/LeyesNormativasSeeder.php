<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeyesNormativasSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Insertar datos en leyes_normativas
        $leyes = [
            [
                'codigo' => 'Pcia Bs As Res 336/03',
                'nombre' => 'Resolución 336/03 - Provincia de Buenos Aires',
                'grupo' => 'Provincia de Buenos Aires',
                'articulo' => null,
                'descripcion' => 'Normativa provincial de Buenos Aires sobre calidad de aguas',
                'variables_aplicables' => 'cloacal, fluvial, absorción por suelo, mar',
                'organismo_emisor' => 'Gobierno de la Provincia de Buenos Aires',
                'fecha_vigencia' => '2003-01-01',
                'fecha_actualizacion' => '2003-01-01',
                'observaciones' => 'Normativa ambiental provincial',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'Ley 24051 Dec 831/93',
                'nombre' => 'Ley Nacional 24051 - Decreto 831/93 Anexo II',
                'grupo' => 'Ley Nacional de Residuos Peligrosos',
                'articulo' => 'Anexo II Tablas',
                'descripcion' => 'Ley de Residuos Peligrosos - Anexo II con tablas de referencia',
                'variables_aplicables' => 'agua con tratamiento, dulce superf, salada superf, agua salobre superf, irrigacion, bebida de ganado, recreacion, pesca industrial',
                'organismo_emisor' => 'Gobierno Nacional',
                'fecha_vigencia' => '1993-01-01',
                'fecha_actualizacion' => '1993-01-01',
                'observaciones' => 'Normativa nacional de residuos peligrosos',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'CAA Art 982',
                'nombre' => 'Código Alimentario Argentino',
                'grupo' => 'Código Alimentario Argentino',
                'articulo' => 'Art. 982',
                'descripcion' => 'Normativa alimentaria nacional - Artículo 982',
                'variables_aplicables' => 'parámetros alimentarios',
                'organismo_emisor' => 'ANMAT - Ministerio de Salud',
                'fecha_vigencia' => null,
                'fecha_actualizacion' => null,
                'observaciones' => 'Código alimentario de aplicación nacional',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'Norma Holandesa 2013',
                'nombre' => 'Norma Holandesa 2013',
                'grupo' => 'Normativa Internacional',
                'articulo' => null,
                'descripcion' => 'Normativa ambiental holandesa para suelos',
                'variables_aplicables' => 'valor objetivo, valor de intervencion',
                'organismo_emisor' => 'Gobierno de los Países Bajos',
                'fecha_vigencia' => '2013-01-01',
                'fecha_actualizacion' => '2013-01-01',
                'observaciones' => 'Normativa internacional de referencia',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'Ley 24051 Anexo 2 Tabla 9',
                'nombre' => 'Ley Nacional 24051 - Anexo 2 Tabla 9',
                'grupo' => 'Ley Nacional de Residuos Peligrosos',
                'articulo' => 'Anexo 2 Tabla 9',
                'descripcion' => 'Ley de Residuos Peligrosos - Anexo 2 Tabla 9 de usos del suelo',
                'variables_aplicables' => 'uso agricola, uso residencial, uso industrial, anexos V y VI',
                'organismo_emisor' => 'Gobierno Nacional',
                'fecha_vigencia' => '1993-01-01',
                'fecha_actualizacion' => '1993-01-01',
                'observaciones' => 'Tabla específica para usos del suelo',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'Ley 24051 Tabla 10',
                'nombre' => 'Ley Nacional 24051 - Tabla 10',
                'grupo' => 'Ley Nacional de Residuos Peligrosos',
                'articulo' => 'Tabla 10',
                'descripcion' => 'Ley de Residuos Peligrosos - Tabla 10 de referencia',
                'variables_aplicables' => 'parámetros específicos tabla 10',
                'organismo_emisor' => 'Gobierno Nacional',
                'fecha_vigencia' => '1993-01-01',
                'fecha_actualizacion' => '1993-01-01',
                'observaciones' => 'Tabla específica de la ley 24051',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'Pcia Bs As Ley 5965 Dec 1074/18',
                'nombre' => 'Ley 5965 - Decreto 1074/18 - Provincia de Buenos Aires',
                'grupo' => 'Provincia de Buenos Aires',
                'articulo' => 'Anexo III Tabla B, Anexo IV Tabla III',
                'descripcion' => 'Normativa ambiental provincial sobre niveles guía y umbrales',
                'variables_aplicables' => 'Anexo III Tabla B Nivel guia, Anexo IV Tabla III Umbral de olor',
                'organismo_emisor' => 'Gobierno de la Provincia de Buenos Aires',
                'fecha_vigencia' => '2018-01-01',
                'fecha_actualizacion' => '2018-01-01',
                'observaciones' => 'Normativa actualizada de la provincia',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'CABA Ley 1356 Dec 198 Res 68/21',
                'nombre' => 'Ley 1356 - Decreto 198 - Resolución 68/21 - CABA',
                'grupo' => 'Ciudad Autónoma de Buenos Aires',
                'articulo' => null,
                'descripcion' => 'Normativa ambiental de la Ciudad Autónoma de Buenos Aires',
                'variables_aplicables' => null,
                'organismo_emisor' => 'Gobierno de la Ciudad de Buenos Aires',
                'fecha_vigencia' => '2021-01-01',
                'fecha_actualizacion' => '2021-01-01',
                'observaciones' => 'Normativa ambiental reciente de CABA',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $leyIds = [];
        foreach ($leyes as $ley) {
            $id = DB::table('leyes_normativas')->insertGetId($ley);
            $leyIds[$ley['codigo']] = $id;
        }

        // Crear variables primero con código
        $variablesData = [
            ['codigo' => 'CLOACAL', 'nombre' => 'cloacal', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'FLUVIAL', 'nombre' => 'fluvial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'ABS_SUELO', 'nombre' => 'absorción por suelo', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'MAR', 'nombre' => 'mar', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'AGUA_TRAT', 'nombre' => 'agua con tratamiento', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'AGUA_DULCE', 'nombre' => 'agua dulce superficial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'AGUA_SALADA', 'nombre' => 'agua salada superficial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'AGUA_SALOBRE', 'nombre' => 'agua salobre superficial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'PARAM_ALIM', 'nombre' => 'parámetros alimentarios', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'VAL_OBJETIVO', 'nombre' => 'valor objetivo', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'VAL_INTERV', 'nombre' => 'valor de intervención', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'USO_AGRIC', 'nombre' => 'uso agrícola', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'USO_RESID', 'nombre' => 'uso residencial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'USO_IND', 'nombre' => 'uso industrial', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'ANEXO3_B', 'nombre' => 'Anexo III Tabla B Nivel guía', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'ANEXO4_III', 'nombre' => 'Anexo IV Tabla III Umbral de olor', 'created_at' => $now, 'updated_at' => $now],
        ];

        $variableIds = [];
        foreach ($variablesData as $variable) {
            $id = DB::table('variables')->insertGetId($variable);
            $variableIds[] = $id;
        }

        // Ahora insertar las relaciones en ley_normativa_variable
        $leyVariables = [
            // Pcia Bs As Res 336/03
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Res 336/03'],
                'variable_id' => $variableIds[0],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Variable cloacal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Res 336/03'],
                'variable_id' => $variableIds[1],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Variable fluvial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Res 336/03'],
                'variable_id' => $variableIds[2],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Absorción por suelo',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Res 336/03'],
                'variable_id' => $variableIds[3],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Variable mar',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Ley 24051 Dec 831/93 Anexo II
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Dec 831/93'],
                'variable_id' => $variableIds[4],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Agua con tratamiento',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Dec 831/93'],
                'variable_id' => $variableIds[5],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Agua dulce superficial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Dec 831/93'],
                'variable_id' => $variableIds[6],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Agua salada superficial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Dec 831/93'],
                'variable_id' => $variableIds[7],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Agua salobre superficial',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Código Alimentario Argentino
            [
                'ley_normativa_id' => $leyIds['CAA Art 982'],
                'variable_id' => $variableIds[8],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Parámetros del Artículo 982',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Norma Holandesa 2013
            [
                'ley_normativa_id' => $leyIds['Norma Holandesa 2013'],
                'variable_id' => $variableIds[9],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Valor objetivo',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Norma Holandesa 2013'],
                'variable_id' => $variableIds[10],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Valor de intervención',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Ley 24051 Anexo 2 Tabla 9
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Anexo 2 Tabla 9'],
                'variable_id' => $variableIds[11],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Uso agrícola',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Anexo 2 Tabla 9'],
                'variable_id' => $variableIds[12],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Uso residencial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Ley 24051 Anexo 2 Tabla 9'],
                'variable_id' => $variableIds[13],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Uso industrial',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Pcia Bs As Ley 5965 Dec 1074/18
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Ley 5965 Dec 1074/18'],
                'variable_id' => $variableIds[14],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Anexo III Tabla B Nivel guía',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ley_normativa_id' => $leyIds['Pcia Bs As Ley 5965 Dec 1074/18'],
                'variable_id' => $variableIds[15],
                'valor_limite' => null,
                'es_obligatoria' => true,
                'observaciones' => 'Anexo IV Tabla III Umbral de olor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('ley_normativa_variable')->insert($leyVariables);

        $this->command->info('Seeder de leyes normativas ejecutado correctamente!');
        $this->command->info('Total leyes insertadas: ' . count($leyes));
        $this->command->info('Total variables insertadas: ' . count($variablesData));
        $this->command->info('Total relaciones ley-variable: ' . count($leyVariables));
    }
}