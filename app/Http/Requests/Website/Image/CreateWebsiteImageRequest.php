<?php

namespace App\Http\Requests\Website\Image;

use App\Http\Requests\Request;
use App\Rules\Website\Image\IsAfterDate;

/**
 * @property mixed $starts_from
 * @property mixed $dealer_id
 */
class CreateWebsiteImageRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => ['integer', 'min:1', 'required', 'exists:dealer,dealer_id'],
            'website_id' => ['integer', 'min:1', 'required', 'exists:website,id,dealer_id,' . $this->dealer_id],
            'title' => ['required', 'string'],
            'image' => ['required', 'url', 'string'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer'],
            'promo_id' => ['nullable', 'integer'],
            'expires_at' => ['nullable', 'date_format:Y-m-d H:i:s', new IsAfterDate($this->starts_from)],
            'starts_from' => ['nullable', 'after_or_equal:today', 'date_format:Y-m-d H:i:s']
        ];
    }
}
