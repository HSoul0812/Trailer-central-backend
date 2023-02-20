<?php

namespace App\Nova\Actions\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

/**
 * Class DefaultValueMappingExport
 * @package App\Nova\Actions\Exports
 */
class DefaultValueMappingExport extends DownloadExcel implements WithHeadings, WithMapping, WithStyles, WithEvents
{
    /**
     * @var string
     */
    public $name = "Export Default Value Mappings";

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Dealer ID',
            'Field',
            'Default Value',
            'Type'
        ];
    }

    /**
     *
     * @param $mapping
     * @return array
     */
    public function map($mapping): array
    {
        return [
            $mapping->dealer_id,
            $mapping->map_from,
            $mapping->map_to,
            $mapping->type
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($event->sheet->getColumnIterator() as $column) {
                    $event->sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
