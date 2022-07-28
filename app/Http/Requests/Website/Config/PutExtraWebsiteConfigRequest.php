<?php

declare(strict_types=1);

namespace App\Http\Requests\Website\Config;

use App\Http\Requests\WithDealerRequest;

/**
 * @property-read integer $website_id
 */
class PutExtraWebsiteConfigRequest extends WithDealerRequest
{
    protected function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id
        ]);
    }
}
