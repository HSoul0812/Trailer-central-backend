<?php

declare(strict_types=1);

namespace App\Repositories\Integration;

use App\Models\Integration\Integration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

use Illuminate\Database\Eloquent\Collection;

class IntegrationRepository implements IntegrationRepositoryInterface
{
    /**
     * @var Integration
     */
    private $model;

    public function __construct(Integration $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $params
     * @return Collection|Integration[]
     */
    public function getAll(array $params): Collection
    {
        if (!isset($params['integrated'])) {
            return $this->model::query()->select('*')->get();
        }

        if ($params['integrated']) {
            $query = $this->model::query()
                ->where('integration.active', 1)
                ->select('integration.*', 'integration_dealer.active', 'integration_dealer.last_run_at as last_updated_at')
                ->join('integration_dealer', 'integration.integration_id', '=', 'integration_dealer.integration_id')
                ->where([
                    ['dealer_id', $params['dealer_id']],
                    ['integration_dealer.active', 1],
                ]);
            $query->orderBy('last_updated_at', 'DESC');
        } else {
            $query = Integration::where('active', 1)->whereDoesntHave('dealers', function ($q) use ($params) {
                return $q->where('dealer.dealer_id', $params['dealer_id']);
            });
        }

        return $query->orderBy('name', 'ASC')->get();
    }

    /**
     * @param array $params
     * @return Integration
     * @throws ModelNotFoundException when `integration_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `integration_id` was not provided
     */
    public function get(array $params): Integration
    {
        if (empty($params['integration_id'])) {
            throw new InvalidArgumentException(sprintf("[%s] 'integration_id' argument is required", __CLASS__));
        }

        $query = $this->model::query()
            ->select('*')
            ->orderBy('name', 'ASC');

        return $query->where('integration_id', $params['integration_id'])->firstOrFail();
    }
}
