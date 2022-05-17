<?php

namespace App\Transformers\Export\Favorites;

use App\Models\Website\User\WebsiteUser;
use League\Fractal\TransformerAbstract;
use function optional;

class CustomerTransformer extends TransformerAbstract
{
    public function transform(WebsiteUser $user): array
    {
        $lastFavoriteInventory = $user->favoriteInventories->last();
        return [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => $user->phone,
            'email_address' => $user->email,
            'terms_and_conditions_accepted' => 'Yes',
            'count_of_favorites' => $user->favoriteInventories->count(),
            'date_created' => optional($user->created_at)->toDateTimeString(),
            'last_login' => optional($user->last_login)->toDateTimeString(),
            'last_update' => optional(optional($lastFavoriteInventory)->created_at)->toDateTimeString()
        ];
    }
}
