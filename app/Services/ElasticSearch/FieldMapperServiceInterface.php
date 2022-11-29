<?php

namespace App\Services\ElasticSearch;

use App\Services\ElasticSearch\Inventory\Builders\FieldQueryBuilderInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;

interface FieldMapperServiceInterface
{
    /**
     * @param Field $field
     * @return FieldQueryBuilderInterface
     */
    public function getBuilder(Field $field): FieldQueryBuilderInterface;
}
