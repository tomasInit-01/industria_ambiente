<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CotizacionSelectorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Iniciando seeder para selectores de cotización...\n";

        // Seeder para Matrices
        $matrices = [
            ['matriz_codigo' => '00082          ', 'matriz_descripcion' => 'CALIDAD DE AIRE', 'matriz_tmuestra' => 'LAB  '],
            ['matriz_codigo' => '00083          ', 'matriz_descripcion' => 'ADMINISTRACION', 'matriz_tmuestra' => 'ADM  '],
            ['matriz_codigo' => '00085          ', 'matriz_descripcion' => 'MEDICIONES AMBIENTALES', 'matriz_tmuestra' => 'MA   '],
            ['matriz_codigo' => '00090          ', 'matriz_descripcion' => 'ANALISIS DE AGUA', 'matriz_tmuestra' => 'LAB  '],
            ['matriz_codigo' => '00095          ', 'matriz_descripcion' => 'MONITOREO CONTINUO', 'matriz_tmuestra' => 'MA   '],
        ];

        try {
            foreach ($matrices as $matriz) {
                DB::table('matriz')->updateOrInsert(
                    ['matriz_codigo' => $matriz['matriz_codigo']],
                    $matriz
                );
            }
            echo "Matrices cargadas exitosamente.\n";
        } catch (\Exception $e) {
            echo "Error al cargar matrices: " . $e->getMessage() . "\n";
        }

        // Seeder para Divisiones/Sectores
        $sectores = [
            ['divis_codigo' => 'LAB  ', 'divis_descripcion' => 'LABORATORIO', 'divis_lab' => true],
            ['divis_codigo' => 'MA   ', 'divis_descripcion' => 'MEDIO AMBIENTE', 'divis_lab' => false],
            ['divis_codigo' => 'IND  ', 'divis_descripcion' => 'INDUSTRIAL', 'divis_lab' => false],
            ['divis_codigo' => 'AGR  ', 'divis_descripcion' => 'AGROPECUARIO', 'divis_lab' => true],
            ['divis_codigo' => 'ALI  ', 'divis_descripcion' => 'ALIMENTOS', 'divis_lab' => true],
            ['divis_codigo' => 'ADM  ', 'divis_descripcion' => 'ADMINISTRACION', 'divis_lab' => false],
        ];

        try {
            foreach ($sectores as $sector) {
                DB::table('divis')->updateOrInsert(
                    ['divis_codigo' => $sector['divis_codigo']],
                    $sector
                );
            }
            echo "Sectores/Divisiones cargados exitosamente.\n";
        } catch (\Exception $e) {
            echo "Error al cargar sectores: " . $e->getMessage() . "\n";
        }

        echo "Seeder completado.\n";
    }
}