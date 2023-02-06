<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Domains\DealerExports\BaseExportAction;
use App\Contracts\DealerExports\EntityActionExportable;
use App\Models\Parts\Vendor;

class VendorsExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'vendors';

    public function getQuery()
    {
        return Vendor::query()->where('dealer_id', $this->dealer->dealer_id);
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'name' => 'Name',
                'business_email' => 'Business Email',
                'business_phone' => 'Business Phone',
                'contact_name' => 'Contact Name',
                'contact_email' => 'Contact Email',
                'contact_phone' => 'Contact Phone',
                'street' => 'Street',
                'city' => 'City',
                'state' => 'State',
                'zip_code' => 'Zip Code',
                'country' => 'Country',
            ])
            ->export();
    }
}
