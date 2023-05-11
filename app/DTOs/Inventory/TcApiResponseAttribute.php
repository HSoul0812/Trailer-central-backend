<?php

namespace App\DTOs\Inventory;

class TcApiResponseAttribute
{
    public int $attribute_id;
    public string $code;
    public string $name;
    public string $type;
    public string $values;
    public ?string $extra_values;
    public ?string $description;
    public ?string $default_value;
    public ?string $aliases;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->attribute_id = $data['attribute_id'];
        $obj->code = $data['code'];
        $obj->name = $data['name'];
        $obj->type = $data['type'];
        $obj->values = $data['values'];
        $obj->extra_values = $data['extra_values'];
        $obj->description = $data['description'];
        $obj->default_value = $data['default_value'];
        $obj->aliases = $data['aliases'];

        return $obj;
    }
}
