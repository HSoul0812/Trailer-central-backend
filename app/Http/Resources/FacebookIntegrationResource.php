<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FacebookIntegrationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'locationId' => $this->dealer_location_id,
            'name' => $this->dealer_name,
            'integration' => $this->id,
            'posts_per_day' => $this->posts_per_day ?? intval(config('marketing.fb.settings.limit.listings', 3)),
            'fb' => [
                'username' => $this->fb_username,
                'password' => $this->fb_password
            ],
            '2fa'=> [
                'type' => $this->tfa_type,
                'username' => $this->tfa_username,
                'password' => $this->tfa_password,
                'code' => $this->tfa_code,
            ]
        ];
    }
}
