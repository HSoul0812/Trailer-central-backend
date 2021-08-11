<?php


namespace App\Utilities\JsonApi;


use App\Exceptions\GenericClientException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\This;

/**
 * Class RequestQueryBuilder
 *
 * A standardized way of building queries based on Requests; you can extend this class to suit your needs
 * Based loosely on the jsonapi spec.
 *
 * Note: this is basically a bridge between http requests and the data layer
 *
 * GET parameters:
 * 1. with - comma separated; indicate what relationships to load (eager loading)
 * 2. filter - apply where clauses
 * 3. sort - comma separated, `column` to sort by `column asc`, `-column` to sort desc
 * 4. limit
 * 5. offset
 *
 * Sample usages:
 *
 * ```
 * public function index(MyRequest $request, RequestQueryBuilder $queryBuilder)
 * {
 *      $query = $queryBuilder
 *          ->request($request)
 *          ->query(MyModel::query())
 *          ->build();
 *
 *      return $query->get();
 * }
 * ```
 *
 * ```
 * public function index(MyRequest $request, MyQueryableRepository $repository)
 * {
 *      $query = $repository
 *          ->withRequest($request)
 *          ->myRepositoryMethod();
 *
 *      return $query->get();
 * }
 * ```

 * Sample query string:
 * ```
 * /api/people?with=friends,addresses&filter[age][gt]=18&sort=name,-age
 * ```
 *
 * @package App\Utilities
 * @todo Add paginator
 */
class QueryBuilder implements QueryBuilderInterface
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
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private $paginator;

    /**
     * RequestQueryBuilder constructor.
     * @param Request|null $request
     * @param Builder|null $query
     */
    public function __construct(Request $request = null, Builder $query = null)
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
        $this
            ->buildRelations()
            ->buildSearch()
            ->buildFilter()
            ->buildFilters()
            ->buildLimit()
            ->buildSort()
            ->buildPagination();

        return $this->query;
    }

    /**
     * Set the request object that should provide the parameters for this builder
     * @param Request $request
     * @return $this
     */
    public function withRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the base query to be built upon; base query can contain e.g. specific where clauses, etc
     * @param Builder $query
     * @return $this
     */
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

    private function buildPagination()
    {
        // if limit and offset are specified, skip this
        if ($this->request->input('limit', null) !== null && $this->request->input('offset', null) !== null)
        {
            return $this;
        }

        $perPage = $this->request->input('per_page', env('JSON_API_DEFAULT_PER_PAGE', 20));

        if ($perPage > 0) {
            $this->paginator = $this->query->paginate($perPage);
        }

        return $this;
    }

    /**
     * apply filters
     *
     * @return $this
     * @throws GenericClientException
     * @todo implement relation whitelist filtering
     * @todo implement filtering on relations
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
        if (!$filterableColumns) { // if empty means all columns are filterable
            return $this;
        }

        foreach ($filter as $column => $operators) {
            // if column is not specified as filterable then skip this filter item
            if (!in_array('*', $filterableColumns) && !in_array($column, $filterableColumns)) continue;

            foreach ($operators as $operator => $value) {
                $operatorFunction = $this->operatorFunction($operator);

                /**
                 * @deprecated It doesn't seem to work. There is no way to ask the developer who implemented this,
                 * so I'll leave it as it is, so that nothing breaks.
                 * Anyway, for queries related to relations, please use ":" as a separator
                 */
                if (strpos($column, '.') !== false) {
                    $pos = strrpos($column, '.');
                    $myRelation = substr($column, 0, $pos);
                    $myColumn = substr($column, $pos + 1);
                    $operatorFunction($myColumn, $value, $this->query->getRelation($myRelation)->getQuery()); // dot means this is a relation

                } elseif (strpos($column, ':') !== false) {
                    $pos = strrpos($column, ':');
                    $myRelation = substr($column, 0, $pos);
                    $myColumn = substr($column, $pos + 1);

                    $this->query->whereHas($myRelation, function ($query) use ($operatorFunction, $myColumn, $value) {
                        $operatorFunction($myColumn, $value, $query);
                    });

                } else {
                    $operatorFunction($column, $value, $this->query); // this is my column
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws GenericClientException
     */
    private function buildFilters()
    {
        $filters = $this->request->input('filters');
        if (!is_array($filters)) {
            return $this;
        }

        // if model is not filterable
        /** @var Filterable $model */
        $model = $this->query->getModel();
        if (!($model instanceof Filterable)) {
            return $this;
        }

        $filterableColumns = $model->jsonApiFilterableColumns();
        if (!$filterableColumns) { // if empty means all columns are filterable
            return $this;
        }

        foreach ($filters as $conditions) {
            $operatorFunctions = [];

            foreach ($conditions as $condition => $filter) {
                foreach ($filter as $column => $operators) {
                    // if column is not specified as filterable then skip this filter item
                    if (!in_array('*', $filterableColumns) && !in_array($column, $filterableColumns)) continue;

                    foreach ($operators as $operator => $value) {
                        $operatorFunctions[] = [
                            'function' => $this->operatorFunction($operator, $condition),
                            'column' => $column,
                            'value' => $value,
                            'isRelation' => strpos($column, '.') !== false,
                            'isRelation2' => strpos($column, ':') !== false,
                        ];
                    }
                }
            }

            $this->query = $this->query->where(function($q) use ($operatorFunctions) {
                foreach ($operatorFunctions as $operatorFunction) {

                    /**
                     * @deprecated It doesn't seem to work. There is no way to ask the developer who implemented this,
                     * so I'll leave it as it is, so that nothing breaks.
                     * Anyway, for queries related to relations, please use ":" as a separator
                     */
                    if ($operatorFunction['isRelation']) {
                        $pos = strrpos($operatorFunction['column'], '.');
                        $myRelation = substr($operatorFunction['column'], 0, $pos);
                        $myColumn = substr($operatorFunction['column'], $pos + 1);
                        $operatorFunction['function']($myColumn, $operatorFunction['value'], $q->getRelation($myRelation)->getQuery()); // dot means this is a relation

                    } elseif ($operatorFunction['isRelation2']) {
                        $pos = strrpos($operatorFunction['column'], '.');
                        $myRelation = substr($operatorFunction['column'], 0, $pos);
                        $myColumn = substr($operatorFunction['column'], $pos + 1);

                        $q->whereHas($myRelation, function ($query) use ($operatorFunction, $myColumn) {
                            $operatorFunction['function']($myColumn, $operatorFunction['value'], $query);
                        });

                    } else {
                        $operatorFunction['function']($operatorFunction['column'], $operatorFunction['value'], $q); // this is my column
                    }
                }
            });
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
        $offsetQuery = $this->request->input('offset');

        if ($limitQuery && $offsetQuery) {
            $this->query->limit($limitQuery);
            $this->query->offset($offsetQuery);
        }

        return $this;
    }

    /**
     * @deprecated
     */
    private function buildOffset()
    {
        $offsetQuery = $this->request->input('offset');
        if (!$offsetQuery) {
            return $this;
        }

        $this->query->offset($offsetQuery);
        return $this;
    }

    private function operatorFunction(string $operator, string $condition = 'and')
    {
        $where = strcasecmp(trim($condition), 'or') === 0 ? 'orWhere' : 'where';

        switch ($operator) {
            case 'eq':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '=', $value);
                };
            case 'lt':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '<', $value);
                };
            case 'lte':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '<=', $value);
                };
            case 'gt':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '>', $value);
                };
            case 'gte':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '>=', $value);
                };
            case 'ne':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, '<>', $value);
                };
            case 'between':
                return function ($column, $value, Builder $query) use ($where) {
                    list($valueFrom, $valueTo) = explode(',', $value);
                    $whereBetween = $where . 'Between';
                    $query->{$whereBetween}($column, [$valueFrom, $valueTo]);
                };
            case 'contains':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, 'LIKE', "%{$value}%");
                };
            case 'notcontains':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, 'NOT LIKE', "%{$value}%");
                };
            case 'startswith':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, 'LIKE', "{$value}%");
                };
            case 'endswith':
                return function ($column, $value, Builder $query) use ($where) {
                    $query->{$where}($column, 'LIKE', "%{$value}");
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

    /**
     * Return the paginator built by this builder
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginator()
    {
        return $this->paginator;
    }
}
