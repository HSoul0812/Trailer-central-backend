<?php

namespace App\Services\Inventory\ESQuery;

use JetBrains\PhpStorm\ArrayShape;

class ESBoolQueryBuilder
{
    public const OCCUR_MUST = 'must';
    public const OCCUR_SHOULD = 'should';
    public const OCCUR_MUST_NOT = 'must_not';

    private array $should;
    private array $mustNot;
    private array $must;

    public function should($queries): self
    {
        $this->should = $queries;

        return $this;
    }

    public function mustNot($queries): self
    {
        $this->mustNot = $queries;

        return $this;
    }

    public function must($queries): self
    {
        $this->must = $queries;

        return $this;
    }

    #[ArrayShape(['bool' => 'array'])]
    public function build(): array
    {
        $query = [];
        if (isset($this->should)) {
            $query[ESBoolQueryBuilder::OCCUR_SHOULD] = $this->should;
        }
        if (isset($this->mustNot)) {
            $query[ESBoolQueryBuilder::OCCUR_MUST_NOT] = $this->mustNot;
        }

        if (isset($this->must)) {
            $query[ESBoolQueryBuilder::OCCUR_MUST] = $this->must;
        }

        return [
          'bool' => $query,
        ];
    }
}
