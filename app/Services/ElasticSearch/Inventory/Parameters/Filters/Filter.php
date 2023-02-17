<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Filters;

use Illuminate\Support\Collection;

class Filter
{
    /** @var string */
    protected $name;

    /** @var Collection<Term> */
    protected $terms;

    /** @var string */
    protected $parentESKeyword;

    /**
     * @param string $name
     * @param array $terms
     */
    public function __construct(string $name, array $terms)
    {
        $this->name = $name;
        $this->terms = collect($terms)->map(function ($term) {
            return Term::fromArray($term);
        });
    }

    /**
     * @param array $data
     * @return Filter
     */
    public static function fromArray(array $data): Filter
    {
        return new static($data['name'], $data['terms']);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getTerms(): Collection
    {
        return $this->terms;
    }

    /**
     * @param string $operator
     * @return void
     */
    public function setParentESOperatorKeyword(string $operator): void
    {
        $this->parentESKeyword = $operator;
    }

    /**
     * @return string
     */
    public function getParentESOperatorKeyword(): string
    {
        return $this->parentESKeyword;
    }
}
