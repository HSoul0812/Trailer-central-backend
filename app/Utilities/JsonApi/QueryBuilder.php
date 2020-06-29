<?php


namespace App\Utilities\JsonApi;


use App\Exceptions\GenericClientException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class RequestQueryBuilder
 *
 * A standardized way of building queries based on Requests; you can extend this class to suit your needs
 * Based loosely on the jsonapi spec.
 *
 * GET parameters:
 * 1. with - comma separated; indicate what relationships to load
 * 2. filter - apply where clauses
 * 3. sort - comma separated, `column` to sort by `column asc`, `-column` to sort desc
 * 4. limit
 * 5. offset
 *
 * Sample usage:
 * ```
 * public function index(MyRequest $request, RequestQueryBuilder $queryBuilder)
 * {
 *      $query = $queryBuild
 *          ->request($request)
 *          ->query(MyModel::query())
 *          ->build();
 *
 *      return $query->get();
 * }
 * ```
 *
 * Sample query string:
 * ```
 * /api/people?with=friends,addresses&filter[age][gt]=18&sort=name,-age
 * ```
 *
 * @package App\Utilities
 * @todo Add paginator
 */
class QueryBuilder
{
    /**
     * @var callable
     */
    private $searchFunction;

    private $operatorFunctions = [];
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Builder
     */
    private $query;

    /**
     * RequestQueryBuilder constructor.
     * @param Request|null $request Http request
     * @param Builder|null $query
     */
    public function __construct(?Request $request, ?Builder $query)
    {
        $this->request = $request;
        $this->query = $query;
    }

    /**
     * Build the query and return
     * @return Builder
     */
    public function build()
    {
        // build all query clauses
        $this->buildRelations()
            ->buildSearch()
            ->buildFilter()
            ->buildLimit()
            ->buildOffset()
            ->buildSort();

        return $this->query;
    }

    public function withRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function withQuery(Builder $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Add a custom search query to the query object
     * @param callable $searchFunction
     * @return $this
     */
    public function withSearch(callable $searchFunction)
    {
        $this->searchFunction = $searchFunction;
        return $this;
    }

    /**
     * Apply the custom search function as added with `withSearch()`
     * @return $this
     */
    private function buildSearch()
    {
        if ($this->searchFunction) {
            call_user_func_array([$this, 'searchFunction'], [$this->request, $this->query]);
        }

        return $this;
    }

    private function buildRelations()
    {
        $with = $this->request->input('with');
        if (!$with) {
            return $this;
        }

            $relations = explode(',', $with);
        foreach ($relations as $relation) {
            $this->query->with($relation);
        }

        return $this;
    }

    /**
     * apply filters
     *
     * @return $this
     * @throws GenericClientException
     * @todo implement relation whitelist filtering
     */
    private function buildFilter()
    {
        $filter = $this->request->input('filter');
        if (!$filter) {
            return $this;
        }

        // if model is not filterable
        /** @var Filterable $model */
        $model = $this->query->getModel();
        if (!($model instanceof Filterable)) {
            return $this;
        }

        $filterableColumns = $model->jsonApiFilterableColumns();
        if (!$filterableColumns) {
            return $this;
        }

        foreach ($filter as $column => $operators) {
            if (!in_array('*', $filterableColumns) && !in_array($column, $filterableColumns)) continue;

            foreach ($operators as $operator => $value) {
                $operatorFunction = $this->operatorFunction($operator);
                $operatorFunction($column, $value, $this->query);
            }
        }

        return $this;
    }

    private function buildSort()
    {
        $sortQuery = $this->request->input('sort');
        if (!$sortQuery) {
            return $this;
        }

        $sortSpecs = explode(',', $sortQuery);
        foreach ($sortSpecs as $spec) {
            $dir = $spec[0] === '-' ? 'DESC' : 'ASC';
            // remove leading dashes to get column name
            $column = preg_replace('/^-/', '', $spec);
            $this->query->orderBy($column, $dir);
        }

        return $this;
    }

    private function buildLimit()
    {
        $limitQuery = $this->request->input('limit');
        if (!$limitQuery) {
            return $this;
        }

        $this->query->limit($limitQuery);
        return $this;
    }

    private function buildOffset()
    {
        $offsetQuery = $this->request->input('offset');
        if (!$offsetQuery) {
            return $this;
        }

        $this->query->offset($offsetQuery);
        return $this;
    }

    private function operatorFunction(string $operator)
    {
        switch ($operator) {
            case 'eq':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '=', $value);
                };
            case 'lt':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '<', $value);
                };
            case 'lte':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '<=', $value);
                };
            case 'gt':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '>', $value);
                };
            case 'gte':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '>=', $value);
                };
            case 'ne':
                return function ($column, $value, Builder $query) {
                    $query->where($column, '<>', $value);
                };
            case 'contains':
                return function ($column, $value, Builder $query) {
                    $query->where($column, 'LIKE', "%{$value}%");
                };
            case 'startswith':
                return function ($column, $value, Builder $query) {
                    $query->where($column, 'LIKE', "{$value}%");
                };
            case 'endswith':
                return function ($column, $value, Builder $query) {
                    $query->where($column, 'LIKE', "%{$value}");
                };
            default:
                if (isset($this->operatorFunctions[$operator])) {
                    return $this->operatorFunctions[$operator];
                } else {
                    throw new GenericClientException("API filter operator {$operator} is invalid");
                }
        }
    }

    /**
     * Add custom operators to filters
     * @param string $operator operator name used in `filter[column_name][$operator]=my+value`
     * @param callable $function a function that performs the operator's operation
     * @return $this
     */
    public function addOperatorFunction(string $operator, $function)
    {
        $this->operatorFunctions[$operator] = $function;
        return $this;
    }
}
