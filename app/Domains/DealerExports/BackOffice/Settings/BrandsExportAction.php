<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\Inventory\Inventory;

/**
 * Class BrandsExportAction
 *
 * @package App\Domains\DealerExports\BackOffice\Settings
 */
class BrandsExportAction extends BaseExportAction implements EntityActionExportable
{
    const ENTITY_TYPE = 'brands';

    public function getQuery()
    {
        return Inventory::query()
            ->selectRaw('DISTINCT brand.id, brand.name as brand, brand.label as label, brand.website as website, brand.address as address, brand.phone as phone, mfgSetting.vendor_id as vendor_id, vendor.name AS vendor_name, mfgSetting.customer_id as customer_id, customer.display_name AS customer_name')
            ->leftJoin('inventory_mfg as brand', function ($query) {
                $query->on('inventory.manufacturer', '=', 'brand.name')->where('inventory.dealer_id', $this->dealer->dealer_id);
            })
            ->leftJoin('dealer_mfg_setting as mfgSetting', function ($query) {
                $query->on('mfgSetting.dealer_id', '=', 'inventory.dealer_id')->on('mfgSetting.inventory_mfg_id', '=', 'brand.id');
            })
            ->leftJoin('qb_vendors as vendor', function ($query) {
                $query->on('mfgSetting.vendor_id', '=', 'vendor.id');
            })
            ->leftJoin('dms_customer as customer', function ($query) {
                $query->on('mfgSetting.customer_id', '=', 'customer.id');
            })
            ->whereRaw('LENGTH(brand.label) > 0')
            ->orderBy('brand.label', 'ASC');
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
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
            ->export();
    }
}
