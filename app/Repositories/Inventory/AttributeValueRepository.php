<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\AttributeValue;

/**
 * Class AttributeValueRepository
 *
 * @package App\Repositories\Inventory
 */
class AttributeValueRepository implements AttributeValueRepositoryInterface
{
    /**
     * @param AttributeValue
     */
    private $model;

    public function __construct(AttributeValue $model)
    {
        $this->model = $model;
    }

    /**
     * @param $params
     *
     * @throw NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     *
     * @throw NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $data
     * @param array $options
     *
     * @return AttributeValue|null
     */
    public function updateOrCreate(array $data, array $options): ?AttributeValue
    {
        if (!empty($options) && !empty($data)) {
            return $this->model->updateOrCreate($options, $data);
        }

        return null;
    }

    /**
     * @param $params
     *
     * @throw NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     *
     * @throw NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param mixed $params
     *
     * @throw NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
