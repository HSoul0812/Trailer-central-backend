<?php

namespace App\Rules;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class AttributeExists implements Rule, DataAwareRule
{
    protected array $data = [];
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(private InventoryServiceInterface $inventoryService)
    {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $attributes = $this->inventoryService->attributes($this->data['entity_type_id']);
        foreach($attributes as $attribute) {
            if($attribute->attribute_id == $value) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function setData($data) {
        $this->data = $data;
    }

    public function message()
    {
        return 'The attribute does not exist';
    }
}
