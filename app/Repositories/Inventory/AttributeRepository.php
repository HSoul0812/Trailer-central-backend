<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\EntityTypeAttribute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AttributeRepository
 * @package App\Repositories\Inventory
 */
class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @param $params
     * @throw NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throw NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throw NotImplementedException
     */
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

    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params)
    {
        if (isset($params['entity_type_id'])) {
            return $this->getAllByEntityTypeId((int)$params['entity_type_id']);
        }
        return Attribute::select('*')->get();
    } 

    /**
     * @param int $entityTypeId
     */
    public function getAllByEntityTypeId(int $entityTypeId)
    {
        /** @var Builder $query */
        $query = Attribute::select('*');
        $query->join(EntityTypeAttribute::getTableName(), Attribute::getTableName().'.attribute_id', '=', EntityTypeAttribute::getTableName().'.attribute_id');
        $query->where(EntityTypeAttribute::getTableName() . '.entity_type_id', '=', $entityTypeId);

        return $query->get();
    }
}
