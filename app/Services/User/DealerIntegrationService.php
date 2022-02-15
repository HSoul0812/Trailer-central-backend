<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\Integration\DealerIntegrationRepositoryInterface;
use App\Repositories\User\Integration\Specific\SpecificIntegrationRepositoryInterface;

class DealerIntegrationService implements DealerIntegrationServiceInterface
{
    /** @var DealerIntegrationRepositoryInterface */
    private $dealerIntegrationRepo;

    public function __construct(DealerIntegrationRepositoryInterface $dealerIntegrationRepo)
    {
        $this->dealerIntegrationRepo = $dealerIntegrationRepo;
    }

    /**
     * @param int $id the integration id
     * @param int $dealerId
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException when there was some problem with the specific class namespace
     */
    public function getValues(int $id, int $dealerId): array
    {
        $dealerIntegration = $this->dealerIntegrationRepo->get([
            'integration_id' => $id,
            'dealer_id' => $dealerId
        ]);

        return $this->getSpecificRepository($dealerIntegration->integration->code)->get([
            'integration_id' => $id,
            'dealer_id' => $dealerId
        ]);
    }

    /**
     * Gets a specific integration repository implementation by integration code.
     * e.g \App\Repositories\User\Integration\Specific\RacingjunkRepository
     *
     * @param string $integrationCode
     * @return SpecificIntegrationRepositoryInterface
     * @throws \Illuminate\Contracts\Container\BindingResolutionException when there was some problem with the specific class namespace
     */
    private function getSpecificRepository(string $integrationCode): SpecificIntegrationRepositoryInterface
    {
        $className = sprintf("\App\Repositories\User\Integration\Specific\%sRepository", ucfirst($integrationCode));

        if (class_exists($className)) {
            return app()->make($className);
        }

        return new class implements SpecificIntegrationRepositoryInterface {
            public function get(array $params): array
            {
                return [];
            }
        };
    }
}
