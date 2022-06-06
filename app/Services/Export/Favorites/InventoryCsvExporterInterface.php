<?php

namespace App\Services\Export\Favorites;

use App\Services\Export\ExporterInterface;
use Illuminate\Support\Collection;

interface InventoryCsvExporterInterface
{
    /**
     * @param Collection $data
     * @return string
     */
    public function export(Collection $data): string;
}
