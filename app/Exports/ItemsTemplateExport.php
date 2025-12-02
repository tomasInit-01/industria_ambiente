<?php

namespace App\Exports;

use App\Models\Metodo;
use App\Models\Matriz;
use App\Models\CotioItems;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ItemsTemplateExport implements WithMultipleSheets
{
    protected $incluirComponentes;

    public function __construct($incluirComponentes = false)
    {
        $this->incluirComponentes = $incluirComponentes;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new ItemsDataSheet(),
            new MetodosListSheet(),
            new MatricesListSheet(),
        ];

        if ($this->incluirComponentes) {
            $sheets[] = new ComponentesListSheet();
        }

        return $sheets;
    }
}

/**
 * Hoja principal con datos de ejemplo
 */
class ItemsDataSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Datos de ejemplo para la plantilla basados en la nueva estructura
        return [
            [
                'LÍQUIDO',  // Tipo (Matriz)
                'EFLUENTE LÍQUIDO',  // Agrupador
                'pH',  // Parámetro (Componente)
                'S.M. 1060 - IRAM 29012',  // Metodología muestreo
                'SM 4500 H+ B',  // Metodología análisis
                'UpH',  // Unidades de medición
                '0.01',  // Límite de detección
                '',  // Límite de cuantificación
                '9100.00'  // Precio de venta
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tipo',
            'Agrupador',
            'Parámetro',
            'Metodología muestreo',
            'Metodología análisis',
            'Unidades de medición',
            'Límite de detección',
            'Límite de cuantificación',
            'Precio de venta'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Configurar la altura de la fila 1
        $sheet->getRowDimension(1)->setRowHeight(45);
        
        // También puedes ajustar el padding de las celdas individualmente si es necesario
        foreach (range('A', 'I') as $column) {
            $sheet->getStyle($column . '1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        
        return [
            1 => [
                'font' => [
                    'bold' => true, 
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Tipo
            'B' => 30,  // Agrupador
            'C' => 30,  // Parámetro
            'D' => 35,  // Metodología muestreo
            'E' => 30,  // Metodología análisis
            'F' => 20,  // Unidades de medición
            'G' => 20,  // Límite de detección
            'H' => 25,  // Límite de cuantificación
            'I' => 18,  // Precio de venta
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Datos';
    }
}

/**
 * Hoja con lista de métodos disponibles
 */
class MetodosListSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * @return array
     */
    public function array(): array
    {
        $metodos = Metodo::orderBy('metodo_codigo')->get();
        
        return $metodos->map(function ($metodo) {
            return [
                $metodo->metodo_codigo,
                $metodo->metodo_descripcion ?? '',
            ];
        })->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Código',
            'Descripción'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Código
            'B' => 50,  // Descripción
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Métodos';
    }
}

/**
 * Hoja con lista de matrices disponibles
 */
class MatricesListSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * @return array
     */
    public function array(): array
    {
        $matrices = Matriz::orderBy('matriz_codigo')->get();
        
        return $matrices->map(function ($matriz) {
            return [
                $matriz->matriz_codigo,
                $matriz->matriz_descripcion ?? '',
            ];
        })->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Código',
            'Descripción'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Código
            'B' => 50,  // Descripción
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Matrices';
    }
}

/**
 * Hoja con lista de componentes actuales (es_muestra = false)
 */
class ComponentesListSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * @return array
     */
    public function array(): array
    {
        $componentes = CotioItems::where('es_muestra', false)
            ->orderBy('id')
            ->get();
        
        return $componentes->map(function ($componente) {
            return [
                $componente->id,
                $componente->cotio_descripcion ?? '',
                $componente->metodo ?? '',
                $componente->matriz_codigo ?? '',
                $componente->precio ?? '0.00',
            ];
        })->toArray();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Método',
            'Matriz',
            'Precio'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7030A0']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 40,  // Nombre
            'C' => 15,  // Método
            'D' => 15,  // Matriz
            'E' => 15,  // Precio
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Componentes';
    }
}
