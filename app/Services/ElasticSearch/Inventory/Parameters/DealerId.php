<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

class DealerId
{
    private const EXCLUSION_DELIMITER = '~';
    private const DELIMITER = ';';

    public const INCLUSION = 'inclusion';
    public const EXCLUSION = 'exclusion';

    /** @var array */
    private $list;

    /** @var string */
    private $type;

    public function __construct(array $list = [], string $type = self::INCLUSION)
    {
        $this->list = $list;
        $this->type = $type;
    }

    /**
     * @param string $dealerIds
     * @return static
     */
    public static function fromString(string $dealerIds): self
    {
        $parts = explode(self::EXCLUSION_DELIMITER, $dealerIds);

        if (count($parts) === 2) {
            return new DealerId(array_filter(explode(self::DELIMITER, $parts[1])), self::EXCLUSION);
        }

        return new DealerId(array_filter(explode(self::DELIMITER, $parts[0])));
    }

    public function list(): array
    {
        return $this->list;
    }

    public function type(): string
    {
        return $this->type;
    }
}
