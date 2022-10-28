<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use App\Models\User\Integration\DealerIntegration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DealerIntegrationRepository implements DealerIntegrationRepositoryInterface
{
    /**
     * @var DealerIntegration
     */
    private $model;

    public function __construct(DealerIntegration $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $params
     * @return Collection|DealerIntegration[]
     * @throws ModelNotFoundException when `dealer_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `dealer_id` was not provided
     */
    public function getAll(array $params): Collection
    {
        if (empty($params['dealer_id'])) {
            throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
        }

        $query = $this->model::query()
            ->select('integration_dealer.*')
            ->join('integration', 'integration.integration_id', '=', 'integration_dealer.integration_id')
            ->orderBy('integration_dealer_id', 'DESC');

        return $query->where('dealer_id', $params['dealer_id'])->get();
    }

    /**
     * @param array $params
     * @return DealerIntegration
     * @throws ModelNotFoundException when `integration_dealer_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `integration_dealer_id` was not provided and some of `integration_id`
     *                                  `dealer_id` were not provided
     */
    public function get(array $params): DealerIntegration
    {
        $query = $this->model::query()
            ->select('integration_dealer.*')
            ->join('integration', 'integration.integration_id', '=', 'integration_dealer.integration_id')
            ->orderBy('integration_dealer_id', 'DESC')
            ->limit(1);

        if (empty($params['integration_dealer_id'])) {
            if (empty($params['integration_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'integration_id' argument is required", __CLASS__));
            }

            if (empty($params['dealer_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
            }

            $query = $query->where('dealer_id', $params['dealer_id'])
                  ->where('integration_dealer.integration_id', $params['integration_id'])
                  ->first();

            if(!$query) {
                return $this->model->newInstance([
                    'dealer_id' => $params['dealer_id'],
                    'integration_id' => $params['integration_id'],
                    'settings' => current(\DB::select(\DB::raw("SELECT settings FROM integration WHERE integration_id = {$params['integration_id']}")))->settings
                ]);
            } else {
                return $query;
            }
        }

        return $query->where('integration_dealer_id', $params['integration_dealer_id'])->firstOrFail();
    }
}
