<?php

namespace App\Services\ElasticSearch;

use App\Services\ElasticSearch\Inventory\Builders\FieldQueryBuilderInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;

interface FieldMapperServiceInterface
{
    /**
     * @param Filter $field
     * @return FieldQueryBuilderInterface
     */
    public function getBuilder(Filter $field): FieldQueryBuilderInterface;
}
