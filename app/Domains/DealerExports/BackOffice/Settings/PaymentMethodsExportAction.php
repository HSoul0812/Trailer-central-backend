<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentMethodsExportAction
 *
 * @package App\Domains\DealerExports\BackOffice\Settings
 */
class PaymentMethodsExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'payment_methods';

    public function getQuery()
    {
        return DB::table('qb_payment_methods')
            ->where([
                'dealer_id' => $this->dealer->dealer_id,
            ]);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'name' => 'Name',
                'type' => 'Type',
                'is_visible' => 'Visible',
                'is_default' => 'Default',
                'is_manually' => 'Manually',
                'created_at' => 'Created Date',
                'updated_at' => 'Updated Date',
                'qb_id' => 'QB ID',
            ])
            ->export();
    }
}
