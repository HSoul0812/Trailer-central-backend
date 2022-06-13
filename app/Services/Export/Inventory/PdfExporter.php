<?php

namespace App\Services\Export\Inventory;

use App\Models\Inventory\Inventory;
use App\Transformers\Inventory\InventoryTransformer;
use Illuminate\Support\Facades\Storage;

class PdfExporter implements ExporterInterface
{
    private function storagePath($inventoryId, $filename): string
    {
        return "inventory-exports/$inventoryId/$filename";
    }

    private function filename($content): string
    {
        $hash = hash('sha256', $content);
        return "$hash.pdf";
    }

    private function getDataToBeExported(Inventory $inventory): array
    {
        $transformer = new InventoryTransformer();
        $data = $transformer->transform($inventory);
        $data['features'] = $transformer->includeFeatures($inventory)->getData()->toArray();
        $data['website'] = $transformer->includeWebsite($inventory)->getData();
        return $data;
    }

    /**
     * @throws \Throwable
     */
    public function export(Inventory $inventory): string
    {
        $inventory = $this->getDataToBeExported($inventory);
        $content = \view('prints.pdf.inventory.index', compact('inventory'))->render();
        $filename = $this->filename($content);
        $path = $this->storagePath($inventory['id'], $filename);

        if (Storage::disk('s3')->exists($path)) {
            return Storage::disk('s3')->url($path);
        }
        //empty dir just in case there is an older file
        Storage::disk('s3')->deleteDirectory("inventory-exports/{$inventory['id']}");

        $engine = app('snappy.pdf.wrapper');
        $engine->loadHTML($content);
        $output = $engine->output();
        Storage::disk('s3')->put($path, $output);
        return Storage::disk('s3')->url($path);
    }
}
