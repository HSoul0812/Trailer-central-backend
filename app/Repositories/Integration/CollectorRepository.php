<?php


namespace App\Repositories\Integration;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Integration\Collector\Collector;
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

    /**
     * @param $params
     * @return Collector
     */
    public function update($params): Collector
    {
        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException('id has been missed. Params - ' . json_encode($params));
        }

        /** @var Collector $collector */
        $collector = Collector::findOrFail($params['id']);

        $collector->fill($params)->save();

        return $collector;
    }
}
