<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

interface FieldQueryBuilderInterface
{
    public function query(): array;
}
