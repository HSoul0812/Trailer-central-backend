<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use InvalidArgumentException;

use App\Repositories\GenericRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\Integration\DealerIntegration;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface DealerIntegrationRepositoryInterface extends GenericRepository
{
    /**
     * @param array $params
     * @return Collection|DealerIntegration[]
     * @throws ModelNotFoundException when `dealer_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `dealer_id` was not provided
     */
    public function getAll(array $params): Collection;

    /**
     * @param array $params
     * @return DealerIntegration
     * @throws ModelNotFoundException when `integration_dealer_id` was provided but there isn't any record with that id
     * @throws InvalidArgumentException when `integration_dealer_id` was not provided and some of `integration_id`
     *                                  `dealer_id` were not provided
     */
    public function get(array $params): DealerIntegration;

    /**
     * @param array $params
     * @return DealerIntegration
     */
    public function update(array $params): DealerIntegration;

    /**
     * @param array $params
     * @return DealerIntegration
     */
    public function delete(array $params): DealerIntegration;

    /**
     * @param array $params
     * @return void
     */
    public function updateAllDealerIntegrations(array $params): void;

    /**
     * @param $integrationId
     * @param $dealerId
     * @param $dealerIntegrationId
     * @return mixed
     */
    public function retrieveDealerIntegration($integrationId, $dealerId, $dealerIntegrationId = null);
}
