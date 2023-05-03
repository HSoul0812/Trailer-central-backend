<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Http\Requests\UpdateRequestInterface;

class UpdateInventoryRequest extends Request implements UpdateRequestInterface
{
    protected array $rules = [
        'inventory_id' => 'required',
        'title' => 'max:255',
        'dealer_location_id' => 'integer',
        'dealer_location_identifier' => 'integer',
        'type_id' => 'required_with:category|integer',
        'category' => 'required_with:type_id|string',
        'existing_images' => 'array|nullable',
        'existing_images.*.image_id' => 'integer|required',
        'existing_images.*.position' => 'integer|nullable',
        'existing_images.*.primary' => 'checkbox|nullable',
        'existing_images.*.is_default' => 'checkbox|nullable',
        'existing_images.*.secondary' => 'checkbox|nullable',
        'existing_images.*.is_secondary' => 'checkbox|nullable',

        'images_to_delete' => 'array|nullable',
        'images_to_delete.*.image_id' => 'integer|required',

        'existing_files' => 'array|nullable',
        'existing_files.*.file_id' => 'integer|required',
        'existing_files.*.position' => 'integer|nullable',
        'existing_files.*.title' => 'string',

        'files_to_delete' => 'array|nullable',
        'files_to_delete.*.file_id' => 'integer|required',

        'update_attributes' => 'bool',
        'update_features' => 'bool',
        'update_clapps' => 'bool',

        'unlock_images' => 'bool',
        'unlock_video' => 'bool',

        'changed_fields_in_dashboard' => 'array|nullable',
        'manual' => 'sometimes|bool',
    ];
}
