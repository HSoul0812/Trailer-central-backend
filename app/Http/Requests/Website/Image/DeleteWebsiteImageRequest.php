<?php

namespace App\Http\Requests\Website\Image;

use App\Http\Requests\Request;

class DeleteWebsiteImageRequest extends Request
{
    public function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'id' => 'integer|min:1|required|exists:website_image,identifier,website_id,' . $this->website_id,
        ];
    }
}
