<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

class DealerLocationIds
{
    public const DELIMITER = ';';
    public const DELIMITER_AGGREGATOR_LIST = '|';

    /** @var array */
    private $locations;

    /** @var array */
    private $locationsForSubAggregatorsFiltering;

    public function __construct(array $locations, ?array $locationsForSubAggregatorsFiltering = null)
    {
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

    /**
     * @param  string  $locationIds
     * @return static
     */
    public static function fromString(string $locationIds): self
    {
        $parts = explode(self::DELIMITER_AGGREGATOR_LIST, $locationIds);

        if (count($parts) === 2) {
            $locationsForSubAggregatorsFiltering = !empty($parts[1]) ?
                array_filter(explode(self::DELIMITER, $parts[1])) :
                null;

            return new self(array_filter(explode(self::DELIMITER, $parts[0])), $locationsForSubAggregatorsFiltering);
        }

        return new self(array_filter(explode(self::DELIMITER, $parts[0])));
    }
}
