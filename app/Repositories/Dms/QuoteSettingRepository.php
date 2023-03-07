<?php

namespace App\Repositories\Dms;

use App\Models\CRM\Dms\Quote\QuoteSetting;
use Illuminate\Support\Arr;

class QuoteSettingRepository implements QuoteSettingRepositoryInterface
{
    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function update($params)
    {
        /** @var QuoteSetting $quoteSetting */
        $quoteSetting = QuoteSetting::where('dealer_id', $params['dealer_id'])->firstOrFail();

        return $quoteSetting->update($params);
    }

    public function get($params)
    {
        // TODO: Implement get() method.
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }
}
