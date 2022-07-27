<?php

declare(strict_types=1);

namespace App\Http\Requests\Website\Config\_Default;

use App\Http\Requests\WithDealerRequest;

class GetWebsiteConfigRequest extends WithDealerRequest
{

    public function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'key' => 'array'
        ]);
    }
}
