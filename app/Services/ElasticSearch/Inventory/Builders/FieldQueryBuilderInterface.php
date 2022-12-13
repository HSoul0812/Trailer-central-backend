<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

interface FieldQueryBuilderInterface
{
    /**
     * @return array
     */
    public function globalQuery(): array;

    /**
     * @return array
     */
    public function generalQuery(): array;
}
