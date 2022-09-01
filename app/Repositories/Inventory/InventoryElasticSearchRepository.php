<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Indexers\ElasticSearchQueryResult;
use App\Models\Inventory\Inventory;

class InventoryElasticSearchRepository implements InventoryElasticSearchRepositoryInterface
{
    private const NORMAL_AGGREGATE_SIZE = 100;

    /** @var Inventory */
    private $model;

    public function search(array $filters = []): ElasticSearchQueryResult
    {
        // @todo probably we need to design a way to make this maintainable, the DW approach
        // is to pull all the filters configuration with its types, then according to the type
        // the query will be built, it is abstracted, but less flexible, so lets try to do it bearing in mind we have
        // more than 120 index searchable properties

        // $search = $this->model::boolSearch();
        // $search->must('match_all', []);
        // $search->filter('term', ['dealerId' => $filters['dealer_id']]);
        // $search->filter('range', ['costOfUnit' => ['gt' => $filters['cost_of_unit']]]);
        // $search->aggregate('category', ['terms' => ['field' => 'category', 'size' => self::NORMAL_AGGREGATE_SIZE]]);
        // etc...
        // Please `App\Repositories\Parts\PartRepository::search` implementation

        // return new ElasticSearchQueryResult($aggregators, $paginatorHints);

        throw new NotImplementedException('Not implemented yet');
    }

    public function __construct(Inventory $model)
    {
        $this->model = $model;
    }
}
