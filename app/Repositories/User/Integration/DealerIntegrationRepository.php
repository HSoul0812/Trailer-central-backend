<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use App\Mail\Integration\DealerIntegrationEmail;
use App\Models\User\Integration\DealerIntegration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Class DealerIntegrationRepository
 *
 * @package App\Repositories\User\Integration
 */
class DealerIntegrationRepository implements DealerIntegrationRepositoryInterface
{
    /**
     * @var DealerIntegration
     */
    private $model;

    /**
     * @param DealerIntegration $model
     */
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
        if (empty($params['integration_dealer_id'])) {
            if (empty($params['integration_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'integration_id' argument is required", __CLASS__));
            }

            if (empty($params['dealer_id'])) {
                throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
            }

            $query = $this->retrieveDealerIntegration($params['integration_id'], $params['dealer_id']);

            if (!$query) {
                return $this->model->newInstance([
                    'dealer_id' => $params['dealer_id'],
                    'integration_id' => $params['integration_id'],
                    'settings' => current(\DB::select(\DB::raw("SELECT settings FROM integration WHERE integration_id = {$params['integration_id']}")))->settings
                ]);
            } else {
                return $query;
            }
        }

        return $this->retrieveDealerIntegration($params['integration_id'], $params['dealer_id'], $params['integration_dealer_id']);
    }

    /**
     * @param array $params
     * @return DealerIntegration
     */
    public function update(array $params): DealerIntegration
    {
        $dealerIntegration = $this->get($params);
        $dealerIntegration->active = $params['active'];

        $dealerIntegration->settings = base64_decode($params['settings']);
        if (!empty($params['location_ids'])) {
            $ids = $params['location_ids'];
            $idString = '';
            foreach ($ids as $id) {
                if (!in_array($id, explode(",", $idString))) {
                    if (!empty($idString)) {
                        $idString .= ',';
                    }
                    $idString .= $id;
                }
            }
            $dealerIntegration->location_ids = $idString;
        } else {
            $dealerIntegration->location_ids = '';
        }

        $dealerIntegration->save();

        return $dealerIntegration;
    }

    /**
     * @param array $params
     * @return DealerIntegration
     */
    public function delete(array $params): DealerIntegration
    {
        $dealerIntegration = $this->retrieveDealerIntegration($params['integration_id'], $params['dealer_id']);
        $deletedDealerIntegration = $dealerIntegration->replicate();

        $deletedDealerIntegration->active = $params['active'];
        $dealerIntegration->delete();

        return $deletedDealerIntegration;
    }

    /**
     * @param $integrationId
     * @param $dealerId
     * @param $dealerIntegrationId
     * @return mixed
     */
    public function retrieveDealerIntegration($integrationId, $dealerId, $dealerIntegrationId = null)
    {
        $query = $this->model::query()
            ->select('integration_dealer.*')
            ->join('integration', 'integration.integration_id', '=', 'integration_dealer.integration_id')
            ->orderBy('integration_dealer_id', 'DESC')
            ->limit(1);

        if (!empty($dealerIntegrationId)) {
            return $query->where('integration_dealer_id', $dealerIntegrationId)->firstOrFail();
        }

        return $query->where('dealer_id', $dealerId)
            ->where('integration_dealer.integration_id', $integrationId)
            ->first();
    }
}
