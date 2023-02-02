<?php

namespace App\Domains\DealerExports;

use App\Domains\DealerExports\BaseExportAction;
use App\Contracts\DealerExports\EntityActionExportable;
use App\Models\Parts\Brand;

class BrandsExporterAction extends BaseExportAction implements EntityActionExportable
{
    public function getQuery()
    {
        return Brand::query()->where('dealer_id', $this->dealer->dealer_id)->get();
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
        $this->setFilename('brands')
            ->setHeaders([
                'brand' => 'Brand',
                'label' => 'Label',
                'website' => 'Website',
                'address' => 'Address',
                'phone' => 'Phone',
                'customer_id' => 'Customer Identifier',
                'customer_name' => 'Customer Name',
                'vendor_id' => 'Vendor Identifier',
                'vendor_name' => 'Vendor Name',
            ])
            ->initiateWriter()
            ->writeHeader()
            ->fetchResults()
            ->writeResults()
            ->generateFile()
            ->uploadFile();

        return $this->storage->url($this->filename);
    }
}
