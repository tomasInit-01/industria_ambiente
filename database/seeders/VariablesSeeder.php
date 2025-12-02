<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variable;

class VariablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variables = [
            // Variables Físico-Químicas
            [
                'codigo' => 'pH',
                'nombre' => 'Potencial de Hidrógeno',
                'descripcion' => 'Medida de acidez o alcalinidad del agua',
                'unidad_medicion' => 'unidades de pH',
                'tipo_variable' => 'Físico-Química',
                'metodo_determinacion' => 'Método potenciométrico con electrodo de vidrio',
                'limite_minimo' => 6.5,
                'limite_maximo' => 8.5,
                'activo' => true
            ],
            [
                'codigo' => 'TURB',
                'nombre' => 'Turbidez',
                'descripcion' => 'Medida de la claridad del agua',
                'unidad_medicion' => 'NTU',
                'tipo_variable' => 'Físico-Química',
                'metodo_determinacion' => 'Método nefelométrico',
                'limite_minimo' => null,
                'limite_maximo' => 3.0,
                'activo' => true
            ],
            [
                'codigo' => 'CLORO_RES',
                'nombre' => 'Cloro Residual',
                'descripcion' => 'Concentración de cloro libre disponible',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Físico-Química',
                'metodo_determinacion' => 'Método colorimétrico DPD',
                'limite_minimo' => 0.2,
                'limite_maximo' => 2.0,
                'activo' => true
            ],
            [
                'codigo' => 'CONDUCT',
                'nombre' => 'Conductividad',
                'descripcion' => 'Capacidad del agua para conducir electricidad',
                'unidad_medicion' => 'µS/cm',
                'tipo_variable' => 'Físico-Química',
                'metodo_determinacion' => 'Método conductimétrico',
                'limite_minimo' => null,
                'limite_maximo' => 1500,
                'activo' => true
            ],

            // Variables Microbiológicas
            [
                'codigo' => 'COLIF_TOT',
                'nombre' => 'Coliformes Totales',
                'descripcion' => 'Bacterias indicadoras de contaminación fecal',
                'unidad_medicion' => 'UFC/100ml',
                'tipo_variable' => 'Microbiológica',
                'metodo_determinacion' => 'Método de filtración por membrana',
                'limite_minimo' => null,
                'limite_maximo' => 0,
                'activo' => true
            ],
            [
                'codigo' => 'COLIF_FEC',
                'nombre' => 'Coliformes Fecales',
                'descripcion' => 'Bacterias indicadoras específicas de contaminación fecal',
                'unidad_medicion' => 'UFC/100ml',
                'tipo_variable' => 'Microbiológica',
                'metodo_determinacion' => 'Método de filtración por membrana a 44.5°C',
                'limite_minimo' => null,
                'limite_maximo' => 0,
                'activo' => true
            ],
            [
                'codigo' => 'ESCHERICHIA',
                'nombre' => 'Escherichia coli',
                'descripcion' => 'Bacteria indicadora de contaminación fecal reciente',
                'unidad_medicion' => 'UFC/100ml',
                'tipo_variable' => 'Microbiológica',
                'metodo_determinacion' => 'Método cromogénico',
                'limite_minimo' => null,
                'limite_maximo' => 0,
                'activo' => true
            ],

            // Variables Químicas
            [
                'codigo' => 'NITRATO',
                'nombre' => 'Nitratos',
                'descripcion' => 'Concentración de iones nitrato',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Química',
                'metodo_determinacion' => 'Método espectrofotométrico',
                'limite_minimo' => null,
                'limite_maximo' => 45,
                'activo' => true
            ],
            [
                'codigo' => 'NITRITO',
                'nombre' => 'Nitritos',
                'descripcion' => 'Concentración de iones nitrito',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Química',
                'metodo_determinacion' => 'Método colorimétrico',
                'limite_minimo' => null,
                'limite_maximo' => 0.1,
                'activo' => true
            ],
            [
                'codigo' => 'AMONIO',
                'nombre' => 'Amonio',
                'descripcion' => 'Concentración de iones amonio',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Química',
                'metodo_determinacion' => 'Método de Nessler',
                'limite_minimo' => null,
                'limite_maximo' => 0.5,
                'activo' => true
            ],

            // Metales Pesados
            [
                'codigo' => 'ARSENICO',
                'nombre' => 'Arsénico',
                'descripcion' => 'Concentración de arsénico total',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Metales Pesados',
                'metodo_determinacion' => 'Espectrofotometría de absorción atómica',
                'limite_minimo' => null,
                'limite_maximo' => 0.01,
                'activo' => true
            ],
            [
                'codigo' => 'PLOMO',
                'nombre' => 'Plomo',
                'descripcion' => 'Concentración de plomo total',
                'unidad_medicion' => 'mg/L',
                'tipo_variable' => 'Metales Pesados',
                'metodo_determinacion' => 'Espectrofotometría de absorción atómica',
                'limite_minimo' => null,
                'limite_maximo' => 0.01,
                'activo' => true
            ]
        ];

        foreach ($variables as $variable) {
            Variable::create($variable);
        }
    }
}
