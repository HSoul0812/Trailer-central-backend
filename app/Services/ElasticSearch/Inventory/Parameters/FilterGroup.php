<?php

namespace App\Services\ElasticSearch\Inventory\Parameters;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use Illuminate\Support\Collection;

class FilterGroup
{
    /** @var string */
    protected $appendTo;

    /** @var string */
    protected $operator;

    /** @var Collection<Filter> */
    protected $fields;

    /** @var string */
    public const APPEND_TO_QUERY = 'query';

    /** @var string */
    public const APPEND_TO_POST_FILTERS = 'post_filter';

    /** @var string */
    private const ES_SHOULD = 'should';

    /** @var string */
    private const ES_MUST = 'must';

    /** @var string */
    public const OPERATOR_AND = 'and';

    /** @var string */
    public const OPERATOR_OR = 'or';

    /**
     * @param array $fields
     * @param string $appendTo
     */
    public function __construct(array $fields, string $appendTo, string $operator)
    {
        $this->appendTo = $appendTo;
        $this->operator = $operator;
        
        $this->fields = collect($fields)->map(function ($field) {
            $filter = Filter::fromArray($field);
            $filter->setParentESOperatorKeyword($this->getESOperatorKeyword());
            return $filter;
        });
    }

    /**
     * @param array $data
     * @return FilterGroup
     */
    public static function fromArray(array $data): FilterGroup
    {
        return new static($data['filters'], $data['append_to'], $data['operator']);
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
     * @return Collection<Filter>
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
