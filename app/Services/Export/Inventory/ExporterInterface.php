<?php

namespace App\Services\Export\Inventory;

use App\Models\Inventory\Inventory;

interface ExporterInterface
{
    /**
     * @param Inventory $inventory
     * @return string
     */
    public function export(Inventory $inventory): string;
}
