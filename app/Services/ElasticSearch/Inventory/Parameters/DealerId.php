<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

class DealerId
{
    private const TYPE_DELIMITER = '|';
    private const DELIMITER = ';';

    /** @var array */
    private $included;

    /** @var array */
    private $excluded;

    /**
     * @param array $included
     * @param array $excluded
     */
    public function __construct(array $included = [], array $excluded = [])
    {
        $this->included = $included;
        $this->excluded = $excluded;
    }

    /**
     * @param string $dealerIds
     * @return static
     */
    public static function fromString(string $dealerIds): self
    {
        $parts = explode(self::TYPE_DELIMITER, $dealerIds);
        if (sizeof($parts) == 2) {
            $included = array_filter(explode(self::DELIMITER, $parts[0]));
            $excluded = array_filter(explode(self::DELIMITER, $parts[1]));
            return new DealerId($included, $excluded);
        }

        return new DealerId(array_filter(explode(self::DELIMITER, $parts[0])));
    }

    /**
     * @return array
     */
    public function includeIds(): array
    {
        return $this->included;
    }

    /**
     * @return array
     */
    public function excludeIds(): array
    {
        return $this->excluded;
    }
}
