<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Attribute;

/**
 * Class AttributeRepository
 * @package App\Repositories\Inventory
 */
class AttributeRepository implements AttributeRepositoryInterface
{

    public function create($params)
    {
        throw new NotImplementedException;
    }

    public function update($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return bool|null
     * @throws \Exception
     */
    public function delete($params)
    {
        /** @var Attribute $attribute */
        $attribute = Attribute::findOrFail($params['attribute_id']);

        foreach ($attribute->attributeValues as $attributeValue) {
            $attributeValue->delete();
        }

        foreach ($attribute->entityTypeAttributes as $entityTypeAttribute) {
            $entityTypeAttribute->delete();
        }

        return $attribute->delete();
    }

    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
