<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Filters;

use Illuminate\Support\Collection;

class Field
{
    /** @var string */
    protected $name;

    /** @var Collection<Term> */
    protected $terms;

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
     * @return Field
     */
    public static function fromArray(array $data): Field
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
}
