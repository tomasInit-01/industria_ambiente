<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSelectorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder para Condiciones de IVA
        $condicionesIva = [
            ['civa_codigo' => 'INSCR', 'civa_descripcion' => 'RESPONSABLE INSCRIPTO', 'civa_abreviada' => 'RI', 'civa_tipo' => 'R', 'civa_letra' => 'A', 'civa_cuit' => true, 'civa_estado' => true],
            ['civa_codigo' => 'MONO ', 'civa_descripcion' => 'MONOTRIBUTO', 'civa_abreviada' => 'MONO', 'civa_tipo' => 'M', 'civa_letra' => 'B', 'civa_cuit' => true, 'civa_estado' => true],
            ['civa_codigo' => 'EXENT', 'civa_descripcion' => 'EXENTO', 'civa_abreviada' => 'EX', 'civa_tipo' => 'E', 'civa_letra' => 'C', 'civa_cuit' => false, 'civa_estado' => true],
            ['civa_codigo' => 'CONF ', 'civa_descripcion' => 'CONSUMIDOR FINAL', 'civa_abreviada' => 'CF', 'civa_tipo' => 'C', 'civa_letra' => 'B', 'civa_cuit' => false, 'civa_estado' => true],
            ['civa_codigo' => 'NOCAT', 'civa_descripcion' => 'NO CATEGORIZADO', 'civa_abreviada' => 'NC', 'civa_tipo' => 'N', 'civa_letra' => 'B', 'civa_cuit' => false, 'civa_estado' => true]
        ];

        foreach ($condicionesIva as $condicion) {
            DB::table('civa')->updateOrInsert(
                ['civa_codigo' => $condicion['civa_codigo']],
                $condicion
            );
        }

        // Seeder para Zonas
        $zonas = [
            ['zon_codigo' => 'NORTE', 'zon_descripcion' => 'ZONA NORTE', 'zon_abreviada' => 'NORTE', 'zon_estado' => true],
            ['zon_codigo' => 'SUR  ', 'zon_descripcion' => 'ZONA SUR', 'zon_abreviada' => 'SUR', 'zon_estado' => true],
            ['zon_codigo' => 'ESTE ', 'zon_descripcion' => 'ZONA ESTE', 'zon_abreviada' => 'ESTE', 'zon_estado' => true],
            ['zon_codigo' => 'OESTE', 'zon_descripcion' => 'ZONA OESTE', 'zon_abreviada' => 'OESTE', 'zon_estado' => true],
            ['zon_codigo' => 'CENTR', 'zon_descripcion' => 'ZONA CENTRO', 'zon_abreviada' => 'CENTRO', 'zon_estado' => true],
            ['zon_codigo' => 'MICRO', 'zon_descripcion' => 'MICROCENTRO', 'zon_abreviada' => 'MICRO', 'zon_estado' => true]
        ];

        foreach ($zonas as $zona) {
            DB::table('zon')->updateOrInsert(
                ['zon_codigo' => $zona['zon_codigo']],
                $zona
            );
        }

        // Seeder para Condiciones de Pago
        $condicionesPago = [
            [
                'pag_codigo' => 'CONT ',
                'pag_descripcion' => 'CONTADO',
                'pag_descuento1' => 0.00,
                'pag_descuento2' => 0.00,
                'pag_interes' => 0.00,
                'pag_cuotas' => 1,
                'pag_dias' => 0,
                'pag_vencimiento' => false,
                'pag_anticipo' => false,
                'pag_clienteproveedor' => 'C',
                'pag_estado' => true
            ],
            [
                'pag_codigo' => 'CTE15',
                'pag_descripcion' => 'CUENTA CORRIENTE 15 DÍAS',
                'pag_descuento1' => 0.00,
                'pag_descuento2' => 0.00,
                'pag_interes' => 0.00,
                'pag_cuotas' => 1,
                'pag_dias' => 15,
                'pag_vencimiento' => true,
                'pag_anticipo' => false,
                'pag_clienteproveedor' => 'C',
                'pag_estado' => true
            ],
            [
                'pag_codigo' => 'CTE30',
                'pag_descripcion' => 'CUENTA CORRIENTE 30 DÍAS',
                'pag_descuento1' => 0.00,
                'pag_descuento2' => 0.00,
                'pag_interes' => 0.00,
                'pag_cuotas' => 1,
                'pag_dias' => 30,
                'pag_vencimiento' => true,
                'pag_anticipo' => false,
                'pag_clienteproveedor' => 'C',
                'pag_estado' => true
            ],
            [
                'pag_codigo' => 'CONTA',
                'pag_descripcion' => 'CONTRA ENTREGA',
                'pag_descuento1' => 0.00,
                'pag_descuento2' => 0.00,
                'pag_interes' => 0.00,
                'pag_cuotas' => 1,
                'pag_dias' => 0,
                'pag_vencimiento' => false,
                'pag_anticipo' => false,
                'pag_clienteproveedor' => 'C',
                'pag_estado' => true
            ]
        ];

        foreach ($condicionesPago as $condicion) {
            DB::table('pag')->updateOrInsert(
                ['pag_codigo' => $condicion['pag_codigo']],
                $condicion
            );
        }

        // Seeder para Listas de Precios
        $listasPrecios = [
            ['lp_codigo' => 'UNO  ', 'lp_descripcion' => 'LISTA PRINCIPAL', 'lp_estado' => true],
            ['lp_codigo' => 'DOS  ', 'lp_descripcion' => 'LISTA SECUNDARIA', 'lp_estado' => true],
            ['lp_codigo' => 'TRES ', 'lp_descripcion' => 'LISTA ESPECIAL', 'lp_estado' => true],
            ['lp_codigo' => 'PROM ', 'lp_descripcion' => 'LISTA PROMOCIONAL', 'lp_estado' => true]
        ];

        // Verificar si la tabla lp existe antes de insertar
        try {
            foreach ($listasPrecios as $lista) {
                DB::table('lp')->updateOrInsert(
                    ['lp_codigo' => $lista['lp_codigo']],
                    $lista
                );
            }
            echo "Listas de precios cargadas exitosamente.\n";
        } catch (\Exception $e) {
            echo "Tabla 'lp' no existe o error al cargar listas de precios: " . $e->getMessage() . "\n";
        }

        echo "Datos de ejemplo cargados exitosamente para selectores de clientes.\n";
    }
}
