<?php

namespace App\Nova\Actions\Imports;

use Schema;
use App\Models\Integration\Collector\Collector;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class CollectorImport
 * @package App\Nova\Actions\Imports
 */
class CollectorImport implements ToModel, WithStartRow, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return Collector
     */
    public function model(array $row): Collector
    {
        $data = [];
        $columns = Schema::getColumnListing('collector');

        foreach ($columns as $column) {
            // Skip column if is not present on this db schema
            if (!isset($row[$column])) {
                continue;
            }

            if ($column == 'overridable_fields') {
                $data[$column] = json_decode($row[$column], true);
            }

            $data[$column] = $row[$column];
        }

        return new Collector($data);
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
}
