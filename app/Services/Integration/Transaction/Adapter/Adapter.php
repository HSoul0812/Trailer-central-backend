<?php

namespace App\Services\Integration\Transaction\Adapter;

use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Models\Inventory\Status;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Feed\Mapping\Incoming\DealerIncomingMappingRepositoryInterface;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Inventory\StatusRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Integration\Transaction\Reference;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\Showroom\ShowroomServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Adapter
 * @package App\Services\Integration\Transaction\Adapter
 */
abstract class Adapter
{
    protected $apiKey = '';
    protected $entityType = '';

    /**
     * @var Collection|null
     */
    protected $conversions = null;

    /**
     * @var Collection|null
     */
    protected $inventoryStatuses = null;

    public const ADAPTER_MAPPING = [
        'Adapter_Utc_Inventory' => 'App\Services\Integration\Transaction\Adapter\Utc\Inventory',
        'Adapter_Pj_Inventory' => 'App\Services\Integration\Transaction\Adapter\Pj\Inventory',
        'Adapter_Bigtex_Inventory' => 'App\Services\Integration\Transaction\Adapter\Bigtex\Inventory',
    ];

    /**
     * @var ApiEntityReferenceRepositoryInterface
     */
    protected $apiEntityReferenceRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var DealerIncomingMappingRepositoryInterface
     */
    protected $dealerIncomingMappingRepository;

    /**
     * @var StatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * @var ShowroomServiceInterface
     */
    protected $showroomService;

    /**
     * @var Reference
     */
    protected $reference;

    public function __construct(
        ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository,
        InventoryServiceInterface $inventoryService,
        InventoryRepositoryInterface $inventoryRepository,
        Reference $reference,
        DealerIncomingMappingRepositoryInterface $dealerIncomingMappingRepository,
        StatusRepositoryInterface $statusRepository,
        ShowroomServiceInterface $showroomService
    ) {
        $this->apiEntityReferenceRepository = $apiEntityReferenceRepository;
        $this->attributeRepository = $attributeRepository;
        $this->userRepository = $userRepository;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
        $this->reference = $reference;
        $this->dealerIncomingMappingRepository = $dealerIncomingMappingRepository;
        $this->statusRepository = $statusRepository;
        $this->showroomService = $showroomService;
    }

    /**
     * @param $entityType
     * @param $referenceId
     * @return false|int
     * @throws BindingResolutionException
     */
    public function getEntityFromReference($entityType = null, $referenceId = null)
    {
        return $this->reference->getEntityFromReference($referenceId, $entityType, $this->apiKey);
    }

    /**
     * @param $entityId
     * @param $referenceId
     * @return void
     */
    public function saveReference($entityId = null, $referenceId = null)
    {
        $this->apiEntityReferenceRepository->create(array(
            'entity_id'    => $entityId,
            'reference_id' => $referenceId,
            'entity_type'  => $this->entityType,
            'api_key'      => $this->apiKey
        ));
    }

    /**
     * @param $type
     * @param $mapFrom
     * @return mixed
     */
    public function convert($type, $mapFrom)
    {
        if ($this->conversions === null) {
            $this->conversions = $this->dealerIncomingMappingRepository->getAll(['integration_name' => $this->apiKey]);
        }

        $searchArrayOrg = [
            [$type, $mapFrom],
            [strtolower($type), $mapFrom],
            [$type, strtolower($mapFrom)],
            [strtolower($type), strtolower($mapFrom)],
        ];

        $searchArray = array_map("unserialize", array_unique(array_map("serialize", $searchArrayOrg)));

        foreach ($searchArray as $item) {
            /** @var DealerIncomingMapping $dealerIncomingMapping */
            $dealerIncomingMapping = $this->conversions->where('type', $item[0])
                ->firstWhere('map_from', $item[1]);

            if ($dealerIncomingMapping instanceof DealerIncomingMapping) {
                return $dealerIncomingMapping->map_to;
            }
        }

        return $mapFrom;
    }

    /**
     * @param string $entityType
     * @param array $attributes
     * @return array
     */
    protected function getInventoryAttributes(string $entityType, array $attributes): array
    {
        $defaultAttributes = $this->attributeRepository
            ->getAllByEntityTypeId($entityType)
            ->pluck('attribute_id', 'code')
            ->toArray();

        $inventoryAttributes = [];

        foreach ($attributes as $name => $value) {
            if (!isset($defaultAttributes[$name])) {
                continue;
            }

            $inventoryAttributes[] = [
                'attribute_id' => $defaultAttributes[$name],
                'value' => $value,
            ];
        }

        return $inventoryAttributes;
    }

    /**
     * @param int|null $statusId
     * @return string
     */
    protected function getStatusLabel(?int $statusId): string
    {
        if ($this->inventoryStatuses === null) {
            $this->inventoryStatuses = $this->statusRepository->getAll();
        }

        /** @var Status $inventoryStatus */
        $inventoryStatus = $this->inventoryStatuses->where('id', '=', $statusId);

        if (!$inventoryStatus instanceof Status) {
            return '';
        }

        return $inventoryStatus->label;
    }
}
