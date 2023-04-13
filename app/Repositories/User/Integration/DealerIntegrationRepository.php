<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\Integration\DealerIntegration;

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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function update(array $params): DealerIntegration
    {
        $dealerIntegration = $this->get($params);
        $dealerIntegration->active = $params['active'];

        if (!empty($params['settings'])) {
            $dealerIntegration->settings = base64_decode($params['settings']);
        }

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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function updateAllDealerIntegrations(array $params): void
    {
        if (empty($params['dealer_id'])) {
            throw new InvalidArgumentException(sprintf("[%s] 'dealer_id' argument is required", __CLASS__));
        }

        $integrations = $this->getAll($params);

        foreach ($integrations as $integration) {
            $this->update([
                'integration_id' => $integration->integration_id,
                'dealer_id' => $params['dealer_id'],
                'active' => $params['active']
            ]);
        }
    }

    /**
     * {@inheritDoc}
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
