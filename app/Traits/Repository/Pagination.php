<?php

namespace App\Traits\Repository;

use ElasticScoutDriverPlus\Builders\SearchRequestBuilder;
use ElasticScoutDriverPlus\SearchResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Trait Pagination
 * @package App\Traits\Repository
 */
trait Pagination
{
    /**
     * @var LengthAwarePaginator|null
     */
    private $paginator;

    /**
     * @return LengthAwarePaginator|null
     */
    public function getPaginator(): ?LengthAwarePaginator
    {
        return $this->paginator;
    }

    /**
     * @param SearchRequestBuilder $search
     * @param int $page
     * @param int $perPage
     * @return SearchResult
     */
    protected function esPaginationExecute(SearchRequestBuilder $search, int $page, int $perPage = 10): SearchResult
    {
        $search->from(($page - 1) * $perPage);
        $search->size($perPage);

        $searchResult = $search->execute();
        $data = $searchResult->models();

        $this->paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $data,
            $searchResult->total(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        return $searchResult;
    }
}
