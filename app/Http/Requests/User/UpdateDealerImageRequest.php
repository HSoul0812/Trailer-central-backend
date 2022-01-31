<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateDealerImageRequest extends Request
{

    protected function getRules(): array
    {
        return [
            'id' => 'integer|min:1|required|exists:website_image,identifier,website_id,' . $this->website_id,
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'title' => 'nullable|string',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'link' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|integer',
            'promo_id' => 'nullable|integer',
            'expires_at' => 'nullable|date_format:Y-m-d H:i:s'
        ];
    }
}
