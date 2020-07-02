<?php


namespace App\Utilities\JsonApi;


use Illuminate\Database\Eloquent\Builder;

/**
 * Trait WithRequestQueryable
 *
 * Adds JsonApi\QueryableRequest capabilities to repositories.
 *
 * To use:
 * 1. Repositories should implement RequestQueryable
 * 2. Add this trait to your repo class
 * 3. Short boilerplate code on repo constructor
 *
 * Check InvoiceRepository for an example.
 *
 * @package App\Utilities\JsonApi
 */
trait WithRequestQueryable
{
    protected $requestQueryableRequest;
    protected $requestQueryableQuery;
    protected $requestQueryableBuilder;

    /**
     * the http request object
     * @param $request
     * @return $this
     */
    public function withRequest($request)
    {
        $this->requestQueryableRequest = $request;
        return $this;
    }

    /**
     * @param Builder $query The base eloquent query
     * @return $this
     */
    public function withQuery(Builder $query)
    {
        $this->requestQueryableQuery = $query;
        return $this;
    }

    /**
     * Return the eloquent query
     * @return Builder|null
     */
    public function query()
    {
        return $this->queryBuilder()
            ->withRequest($this->requestQueryableRequest)
            ->withQuery($this->requestQueryableQuery)
            ->build();
    }

    private function queryBuilder()
    {
        if (!$this->requestQueryableBuilder) {
            $this->requestQueryableBuilder = new QueryBuilder();
        }

        return $this->requestQueryableBuilder;
    }

    public function getPaginator()
    {
        return $this->queryBuilder()->paginator();
    }

}
