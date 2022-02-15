<?php


namespace App\Repositories\Integration;

use App\Exceptions\NotImplementedException;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CollectorRepository
 * @package App\Repositories\Integration
 */
class CollectorRepository extends RepositoryAbstract implements CollectorRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    /**
     * @param array $params
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|null[]
     */
    public function getAll($params)
    {
        $query = $this->query();

        $query->with(['specifications' => function ($query) {
            $query->with(['rules', 'actions']);
        }]);

        return $query->get();
    }
}
