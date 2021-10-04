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
    protected $paginator;

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
     * @param array $params
     * @return SearchResult
     */
    protected function esPagination(SearchRequestBuilder $search, int $page, int $perPage = 10, array $params = []): SearchResult
    {
        $search->from(($page - 1) * $perPage);
        $search->size($perPage);

        $searchResult = $search->execute();
        $data = $searchResult->models();

        $total = (isset($params['aggregationTotal']) && $params['aggregationTotal']) ? $searchResult->aggregations()['total']['value'] : $searchResult->total();

        $this->initPaginator($data, $total, $page, $perPage);

        return $searchResult;
    }

    /**
     * @param mixed $data
     * @param int $total
     * @param int $page
     * @param int $perPage
     */
    protected function initPaginator($data, int $total, int $page, int $perPage = 10)
    {
        $this->paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $data,
            $total,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
