<?php

namespace App\Http\Requests\Website\Image;

use App\Http\Requests\Request;

class GetWebsiteImageRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'expired' => 'in:1,0',
            'expires_at' => 'date_format:Y-m-d'
        ];
    }
}
