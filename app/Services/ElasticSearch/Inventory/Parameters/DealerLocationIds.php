<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

class DealerLocationIds
{
    public const DELIMITER = ';';
    public const DELIMITER_OPTION = '|';

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
     * @param string $locationExpression a value like 1|123;234 or 0|123;234|234;567 when the locations for the sub aggregator were provided
     * @return static
     */
    public static function fromString(string $locationExpression): self
    {
        $parts = explode(self::DELIMITER_OPTION, $locationExpression);

        if (count($parts) === 3) {
            $locationsForSubAggregatorsFiltering = !empty($parts[2]) ?
                array_filter(explode(self::DELIMITER, $parts[2])) :
                null;

            return new self(
                (bool)$parts[0],
                array_filter(explode(self::DELIMITER, $parts[1])),
                $locationsForSubAggregatorsFiltering
            );
        }

        return new self((bool)$parts[0], array_filter(explode(self::DELIMITER, $parts[1])));
    }
}
