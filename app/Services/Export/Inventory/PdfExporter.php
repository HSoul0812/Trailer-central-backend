<?php

namespace App\Services\Export\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Website;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
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

    private function getDealerLogo(Website $website)
    {
        $template = $website->template;

        if (@file_get_contents($logo = "http://dealer-cdn.s3.amazonaws.com/skin/website/responsive/$template/images/logo.png")) {
            return $logo;
        }

        if (@file_get_contents($logo = "http://d22w5hvhpkzo8j.cloudfront.net/skin/website/desktop/$template/images/logo.png")) {
            return $logo;
        }

        $config = app(WebsiteConfigRepositoryInterface::class)->getAll([
            'website_id' => $website->id,
            'key' => WebsiteConfig::INVENTORY_PRINT_LOGO_KEY
        ]);
        if ($logo = $config->first()) {
            return $logo->value;
        }
        return null;
    }

    private function getDataToBeExported(Inventory $inventory): array
    {
        $transformer = new InventoryTransformer();
        $data = $transformer->transform($inventory);
        $data['features'] = $transformer->includeFeatures($inventory)->getData()->toArray();
        $data['website'] = $transformer->includeWebsite($inventory)->getData();
        $data['dealer_logo'] = $this->getDealerLogo($data['website']);
        $data['attributes'] = $inventory->attributeValues;
        $data['width_inches'] = $data['width_inches'] ? $this->dimensionToFeetAndInches($data['width_inches'], 'inches') : null;
        $data['width_second'] = $data['width_second'] ? $this->dimensionToFeetAndInches($data['width_second'], 'feet', 'inches') : null;
        $data['width_inches_second'] = $data['width_inches_second'] ? $this->dimensionToFeetAndInches($data['width_inches_second'], 'inches') : null;
        $data['length_inches'] = $data['length_inches'] ? $this->dimensionToFeetAndInches($data['length_inches'], 'inches') : null;
        $data['length_second'] = $data['length_second'] ? $this->dimensionToFeetAndInches($data['length_second'], 'feet', 'inches') : null;
        $data['length_inches_second'] = $data['length_inches_second'] ? $this->dimensionToFeetAndInches($data['length_inches_second'], 'inches') : null;
        $data['height_inches'] = $data['height_inches'] ? $this->dimensionToFeetAndInches($data['height_inches'], 'inches') : null;
        $data['height_second'] = $data['height_second'] ? $this->dimensionToFeetAndInches($data['height_second'], 'feet', 'inches') : null;
        $data['height_inches_second'] = $data['height_inches_second'] ? $this->dimensionToFeetAndInches($data['height_inches_second'], 'inches') : null;
        return $data;
    }

    private function dimensionToFeetAndInches($dimension, $display = 'feet', $from = 'default')
    {
        if ($from === 'default') {
            $from = $display;
        }
        if ($from === 'inches') {
            $inFull = floatval($dimension);
            $feet = floor($inFull / 12);
            $inches = $inFull % 12;
        } else {
            $feet = intval($dimension);
            $ftFull = floatval($dimension);
            $inches = $ftFull - $feet;
            $inFull = ($ftFull * 12);
        }
        if ($display === 'inches') {
            return "$inFull\"";
        } else {
            if ($inches > 0) {
                return "$feet' $inches\"";
            } else {
                return "$feet'";
            }
        }
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
        $output = $engine->setOption('footer-right', '[page]/[sitepages]')
            ->loadHTML($content)->output();
        Storage::disk('s3')->put($path, $output);
        return Storage::disk('s3')->url($path);
    }
}
