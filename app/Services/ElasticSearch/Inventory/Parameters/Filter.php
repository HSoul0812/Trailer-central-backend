<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;
use Illuminate\Support\Collection;

class Filter
{
    /** @var string */
    protected $appendTo;

    /** @var Collection<Field> */
    protected $fields;

    /** @var string */
    protected const APPEND_TO_QUERY = 'query';

    /** @var string */
    protected const APPEND_TO_POST_FILTERS = 'post_filter';

    /**
     * @param array $fields
     * @param string $appendTo
     */
    public function __construct(array $fields, string $appendTo)
    {
        $this->appendTo = $appendTo;
        $this->fields = collect($fields)->map(function ($field) {
            return Field::fromArray($field);
        });
    }

    /**
     * @param array $data
     * @return Filter
     */
    public static function fromArray(array $data): Filter
    {
        return new static($data['fields'], $data['append_to']);
    }

    /**
     * @return bool
     */
    public function appendsToQuery(): bool
    {
        return $this->appendTo == self::APPEND_TO_QUERY;
    }

    /**
     * @return bool
     */
    public function appendsToPostFilters(): bool
    {
        return $this->appendTo == self::APPEND_TO_POST_FILTERS;
    }

    /**
     * @return Collection<Field>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }
}
