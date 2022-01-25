<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use App\Models\User\Integration\DealerIntegration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

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
     * @return DealerIntegration
     * @throws ModelNotFoundException when `integration_dealer_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `integration_dealer_id` was not provided and some of `integration_id`
     *                                  `dealer_id` were not provided
     */
    public function get(array $params): DealerIntegration
    {
        if (empty($params['integration_dealer_id'])) {
            if (empty($params['integration_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
            }

            if (empty($params['dealer_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
            }

            return $this->model
                ->where('dealer_id', $params['dealer_id'])
                ->where('integration_id', $params['integration_id'])->firstOrFail();
        }

        return $this->model::findOrFail($params['integration_dealer_id']);
    }
}
