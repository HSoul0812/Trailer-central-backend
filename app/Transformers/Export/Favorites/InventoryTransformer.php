<?php

namespace App\Transformers\Export\Favorites;

class InventoryTransformer
{
    public function transform($inventory): array
    {
        return [
            'first_name' => $inventory->user->first_name,
            'last_name' => $inventory->user->last_name,
            'phone_number' => $inventory->user->phone,
            'email_address' => $inventory->user->email,
            'terms_and_conditions_accepted' => 'Yes',
            'count_of_favorites' => $inventory->user->favoriteInventories->count(),
            'date_created' => optional($inventory->user->created_at)->toDateTimeString(),
            'last_login' => optional($inventory->user->last_login)->toDateTimeString(),
            'last_update' => optional(optional($inventory->user->favoriteInventories->last())->created_at)->toDateTimeString(),
            'stock' => $inventory->stock,
            'vin' => $inventory->vin,
            'location' => $inventory->dealerLocation->name,
            'condition' => $inventory->condition,
            'type' => $inventory->entityType->title,
            'category' => $inventory->category,
            'title' => $inventory->title,
            'year' => $inventory->year,
            'manufacturer' => $inventory->manufacturer,
            'status' => $inventory->status_label,
            'msrp' => $inventory->msrp,
            'model' => $inventory->model,
            'price' => $inventory->price,
            'sales_price' => $inventory->sales_price,
            'hidden_price' => $inventory->hidden_price
        ];
    }
}
