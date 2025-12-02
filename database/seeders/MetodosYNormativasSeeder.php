<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetodosYNormativasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Seeders para métodos de muestreo
        DB::table('metodos_muestreo')->insert([
            [
                'codigo' => 'MUE001',
                'nombre' => 'Muestreo Manual Simple',
                'descripcion' => 'Muestreo manual básico para análisis general',
                'equipo_requerido' => 'Recipientes estériles, guantes',
                'procedimiento' => 'Tomar muestra directamente del punto de origen',
                'unidad_medicion' => 'ml',
                'costo_base' => 150.00,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'MUE002',
                'nombre' => 'Muestreo Automático',
                'descripcion' => 'Muestreo automatizado con equipos especializados',
                'equipo_requerido' => 'Muestreador automático, sensores',
                'procedimiento' => 'Configurar equipo automático según protocolo',
                'unidad_medicion' => 'ml',
                'costo_base' => 300.00,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'MUE003',
                'nombre' => 'Muestreo Compuesto',
                'descripcion' => 'Muestreo de múltiples puntos para análisis integral',
                'equipo_requerido' => 'Múltiples recipientes, GPS',
                'procedimiento' => 'Tomar muestras de varios puntos según protocolo',
                'unidad_medicion' => 'ml',
                'costo_base' => 250.00,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seeders para métodos de análisis
        DB::table('metodos_analisis')->insert([
            [
                'codigo' => 'ANA001',
                'nombre' => 'Análisis Fisicoquímico Básico',
                'descripcion' => 'Análisis básico de parámetros fisicoquímicos',
                'equipo_requerido' => 'pH metro, conductímetro, turbidímetro',
                'procedimiento' => 'Medición directa de parámetros básicos',
                'unidad_medicion' => 'mg/L',
                'limite_deteccion_default' => 0.01,
                'limite_cuantificacion_default' => 0.03,
                'costo_base' => 500.00,
                'tiempo_estimado_horas' => 2,
                'requiere_calibracion' => true,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'ANA002',
                'nombre' => 'Cromatografía Líquida HPLC',
                'descripcion' => 'Análisis por cromatografía líquida de alta resolución',
                'equipo_requerido' => 'HPLC, columnas específicas, solventes',
                'procedimiento' => 'Preparación de muestra y análisis cromatográfico',
                'unidad_medicion' => 'µg/L',
                'limite_deteccion_default' => 0.001,
                'limite_cuantificacion_default' => 0.003,
                'costo_base' => 1200.00,
                'tiempo_estimado_horas' => 4,
                'requiere_calibracion' => true,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'ANA003',
                'nombre' => 'Espectrofotometría UV-Vis',
                'descripcion' => 'Análisis espectrofotométrico ultravioleta-visible',
                'equipo_requerido' => 'Espectrofotómetro UV-Vis, cubetas',
                'procedimiento' => 'Medición de absorbancia a longitudes específicas',
                'unidad_medicion' => 'mg/L',
                'limite_deteccion_default' => 0.005,
                'limite_cuantificacion_default' => 0.015,
                'costo_base' => 800.00,
                'tiempo_estimado_horas' => 3,
                'requiere_calibracion' => true,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'ANA004',
                'nombre' => 'Análisis Microbiológico',
                'descripcion' => 'Análisis de microorganismos patógenos',
                'equipo_requerido' => 'Incubadora, medios de cultivo, microscopio',
                'procedimiento' => 'Cultivo e identificación microbiológica',
                'unidad_medicion' => 'UFC/100ml',
                'limite_deteccion_default' => 1.0,
                'limite_cuantificacion_default' => 3.0,
                'costo_base' => 600.00,
                'tiempo_estimado_horas' => 48,
                'requiere_calibracion' => false,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seeders para leyes y normativas
        DB::table('leyes_normativas')->insert([
            [
                'codigo' => 'CAA982',
                'nombre' => 'Código Alimentario Argentino - Artículo 982',
                'grupo' => 'Código Alimentario Argentino',
                'articulo' => 'Art. 982',
                'descripcion' => 'Normas para agua potable y análisis fisicoquímicos',
                'variables_aplicables' => 'pH, turbidez, cloro residual, coliformes',
                'organismo_emisor' => 'ANMAT',
                'fecha_vigencia' => '2020-01-01',
                'fecha_actualizacion' => '2023-06-15',
                'observaciones' => 'Aplicable a análisis de agua potable',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'CAA983',
                'nombre' => 'Código Alimentario Argentino - Artículo 983',
                'grupo' => 'Código Alimentario Argentino',
                'articulo' => 'Art. 983',
                'descripcion' => 'Normas para análisis microbiológicos en agua',
                'variables_aplicables' => 'Coliformes totales, E.coli, enterococos',
                'organismo_emisor' => 'ANMAT',
                'fecha_vigencia' => '2020-01-01',
                'fecha_actualizacion' => '2023-06-15',
                'observaciones' => 'Aplicable a análisis microbiológicos',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'LEY24051',
                'nombre' => 'Ley Nacional de Residuos Peligrosos',
                'grupo' => 'Legislación Ambiental',
                'articulo' => 'Ley 24.051',
                'descripcion' => 'Régimen de desechos peligrosos',
                'variables_aplicables' => 'Metales pesados, compuestos orgánicos, pH',
                'organismo_emisor' => 'Congreso Nacional',
                'fecha_vigencia' => '1992-01-17',
                'fecha_actualizacion' => '2022-03-10',
                'observaciones' => 'Aplicable a residuos industriales',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'RES831',
                'nombre' => 'Resolución 831/93 - Niveles Guía de Calidad de Agua',
                'grupo' => 'Resoluciones Ambientales',
                'articulo' => 'Res. 831/93',
                'descripcion' => 'Niveles guía de calidad de agua para diferentes usos',
                'variables_aplicables' => 'DBO, DQO, sólidos suspendidos, nutrientes',
                'organismo_emisor' => 'Secretaría de Ambiente',
                'fecha_vigencia' => '1993-10-15',
                'fecha_actualizacion' => '2021-11-20',
                'observaciones' => 'Aplicable a efluentes y cuerpos de agua',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
