<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\AttributeValue;
use App\Repositories\Repository;

/**
 * Interface AttributeValueRepositoryInterface
 *
 * @package App\Repositories\Inventory
 */
interface AttributeValueRepositoryInterface extends Repository
{
    /**
     * Updates or create the record
     *
     * @param array $data
     * @param array $options
     *
     * @return AttributeValue
     */
    public function updateOrCreate(array $data, array $options): ?AttributeValue;
}
