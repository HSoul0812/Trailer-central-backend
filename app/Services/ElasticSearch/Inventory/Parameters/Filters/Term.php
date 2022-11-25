<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Filters;

class Term
{
    /** @var string */
    protected $operator;

    /** @var array */
    protected $values;

    /** @var string */
    private const ES_SHOULD = 'should';

    /** @var string */
    private const ES_MUST_NOT = 'must_not';

    /** @var string */
    private const OPERATOR_EQ = 'eq';

    /** @var string */
    private const OPERATOR_NEQ = 'eq';

    /**
     * @param string $operator
     * @param array $values
     */
    public function __construct(string $operator, array $values)
    {
        $this->operator = $operator;
        $this->values = $values;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): Term
    {
        return new static($data['operator'], $data['values']);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function getESOperatorKeyword(): string
    {
        return [self::OPERATOR_EQ => self::ES_SHOULD, self::OPERATOR_NEQ => self::ES_MUST_NOT][$this->operator];
    }
}
