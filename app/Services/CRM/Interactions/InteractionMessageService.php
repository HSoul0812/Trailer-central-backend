<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Interactions\InteractionMessageException;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class InteractionMessageService
 * @package App\Services\CRM\Interactions
 */
class InteractionMessageService implements InteractionMessageServiceInterface
{
    /**
     * @var InteractionMessageRepositoryInterface
     */
    private $interactionMessageRepository;

    /**
     * @param InteractionMessageRepositoryInterface $interactionMessageRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionMessageRepository)
    {
        $this->interactionMessageRepository = $interactionMessageRepository;
    }

    /**
     * @param array $params
     * @return bool
     *
     * @throws InteractionMessageException
     */
    public function bulkUpdate(array $params): bool
    {
        $repository = $this->interactionMessageRepository;

        try {
            if (!empty($params['search_params']) && is_array($params['search_params'])) {
                $searchParams = $params['search_params'];

                $searchParams['size'] = 10000;

                $ids = array_column($repository->search($searchParams), 'id');

                $params['ids'] = array_merge($ids, $params['ids'] ?? []);

                if (empty($params['ids'])) {
                    return false;
                }

                unset($params['search_params']);
            }

            return $repository->bulkUpdate($params);

        } catch (\Exception $e) {
            Log::error('Interaction message bulk update error. Message - ' . $e->getMessage() , $e->getTrace());
            throw new InteractionMessageException('Inventory item create error');
        }
    }
}
