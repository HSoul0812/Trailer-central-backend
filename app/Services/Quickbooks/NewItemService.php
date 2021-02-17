<?php

namespace App\Services\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\ItemNew;
use App\Models\User\User;

/**
 * Class NewItemService
 *
 * @package App\Services\Quickbooks
 */
class NewItemService
{
    public function getByItemName(int $dealerId, string $itemName)
    {
        $dealer = User::findOrFail($dealerId);
        $newItem=  ItemNew::where([
            ['dealer_id', '=', $dealerId],
            ['name', '=', $itemName],
            ['is_default', '=', $dealer->is_default_quickbook_settings]
        ])->firstOrFail();

        return $newItem;
    }
}
