<?php

namespace App\Services\ElasticSearch;

use App\Services\ElasticSearch\Inventory\Builders\FieldQueryBuilderInterface;

interface FieldMapperServiceInterface
{
    public function getBuilder(string $field, string $data): FieldQueryBuilderInterface;
}
