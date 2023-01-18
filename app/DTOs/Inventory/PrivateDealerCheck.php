<?php

namespace App\DTOs\Inventory;

class PrivateDealerCheck
{
    const FROM_TT = 'trailertrader';
    const FROM_TC = 'trailercentral';
    const PRIVATE_DEALER_IDS = [
        8410,
        1004,
        12213,
        10005
    ];

    public function checkArray(array $dealer): bool
    {
        if(!isset($dealer)) {
            return false;
        }

        return $this->isDealerFromTT($dealer) || $this->isDealerInList($dealer);
    }

    private function isDealerFromTT(array $dealer): bool
    {
        return isset($dealer['from']) && $dealer['from'] === self::FROM_TT;
    }

    private function isDealerInList(array $dealer): bool
    {
        return isset($dealer['id']) && in_array(intval($dealer['id']), self::PRIVATE_DEALER_IDS);
    }
}
