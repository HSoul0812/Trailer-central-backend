<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;
use Illuminate\Support\Collection;

class FilterGroup
{
    /** @var string */
    protected $appendTo;

    /** @var string */
    protected $operator;

    /** @var Collection<Field> */
    protected $fields;

    /** @var string */
    protected const APPEND_TO_QUERY = 'query';

    /** @var string */
    protected const APPEND_TO_POST_FILTERS = 'post_filter';

    /** @var string */
    private const ES_SHOULD = 'should';

    /** @var string */
    private const ES_MUST = 'must';

    /** @var string */
    private const OPERATOR_AND = 'and';

    /** @var string */
    private const OPERATOR_OR = 'or';

    /**
     * @param array $fields
     * @param string $appendTo
     */
    public function __construct(array $fields, string $appendTo, string $operator)
    {
        $this->appendTo = $appendTo;
        $this->fields = collect($fields)->map(function ($field) {
            return Field::fromArray($field);
        });
        $this->operator = $operator;
    }

    /**
     * @param array $data
     * @return FilterGroup
     */
    public static function fromArray(array $data): FilterGroup
    {
        return new static($data['fields'], $data['append_to'], $data['operator']);
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

    /**
     * @return string
     */
    public function getESOperatorKeyword(): string
    {
        return [self::OPERATOR_OR => self::ES_SHOULD, self::OPERATOR_AND => self::ES_MUST][$this->operator];
    }
}
