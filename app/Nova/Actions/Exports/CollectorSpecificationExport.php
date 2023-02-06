<?php

namespace App\Nova\Actions\Exports;

use App\Models\Integration\Collector\Collector;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

/**
 * Class CollectorSpecificationExport
 * @package App\Nova\Actions\Exports
 */
class CollectorSpecificationExport extends DownloadExcel implements WithHeadings, WithMapping, WithStyles, WithEvents
{
    /**
     * @var string
     */
    public $name = "Export Collector Specifications";

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Collector ID',
            'Logical Operator',
            'Created At',
            'Updated At'
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
            $mapping->id,
            $mapping->collector_id,
            $mapping->logical_operator,
            $mapping->created_at,
            $mapping->updated_at
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
