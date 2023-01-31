<?php

namespace App\Domains\DealerExports;

use App\Domains\DealerExports\BaseExportAction;
use App\Contracts\DealerExports\EntityActionExportable;
use App\Models\Parts\Vendor;

class VendorsExporterAction extends BaseExportAction implements EntityActionExportable
{
    public function getQuery()
    {
        return Vendor::query()->where('dealer_id', $this->dealer->dealer_id)->get();
    }

    protected function fetchResults()
    {
        $this->rows = $this->getQuery()->get();

        return $this;
    }

    public function transformRow($row)
    {
        $headers = array_keys($this->headers);

        return array_map(function (string $header) use ($row) {
            return object_get($row, $header);
        }, $headers);
    }

    protected function writeResults()
    {
        $this->writeData([$this, 'transformRow']);

        return $this;
    }

    public function execute(): string
    {
        $this->setFilename('vendors')
            ->setHeaders([])
            ->initiateWriter()
            ->writeHeader()
            ->fetchResults()
            ->writeResults()
            ->generateFile()
            ->uploadFile();

        return $this->storage->url($this->filename);
    }
}
