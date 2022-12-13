<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

class DealerLocationIds
{
    /** @var array */
    private $locations;

    /** @var array */
    private $locationsForSubAggregatorsFiltering;

    /** @var array determines if the filter should append to `post_filters` */
    private $isFilterable;

    public function __construct(bool $isFilterable, array $locations, ?array $locationsForSubAggregatorsFiltering = null)
    {
        $this->isFilterable = $isFilterable;
        $this->locations = $locations;
        $this->locationsForSubAggregatorsFiltering = $locationsForSubAggregatorsFiltering ?? $locations;
    }

    public function locations(): array
    {
        return $this->locations;
    }

    public function locationsForSubAggregatorsFiltering(): array
    {
        return $this->locationsForSubAggregatorsFiltering;
    }

    public function isFilterable(): bool
    {
        return $this->isFilterable;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['is_filterable']) && !isset($data['locations_aggregators'])) {
            return new self(false, $data, []);
        }
        return new self($data['is_filterable'], $data['locations'], $data['locations_aggregators']);
    }
}
