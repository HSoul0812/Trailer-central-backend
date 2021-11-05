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
                $ids = $this->getIds($params['search_params']);

                $params['ids'] = array_merge($ids, $params['ids'] ?? []);

                if (empty($params['ids'])) {
                    return false;
                }

                unset($params['search_params']);
            }

            return $repository->bulkUpdate($params);

        } catch (\Exception $e) {
            Log::error('Interaction message bulk update error. Message - ' . $e->getMessage() , $e->getTrace());
            throw new InteractionMessageException('Interaction message bulk update error');
        }
    }

    /**
     * @param array $params
     * @return bool
     *
     * @throws InteractionMessageException
     */
    public function bulkSearchable(array $params): bool
    {
        $repository = $this->interactionMessageRepository;

        try {
            if (!empty($params['search_params']) && is_array($params['search_params'])) {
                $ids = $this->getIds($params['search_params']);

                $params['ids'] = array_merge($ids, $params['ids'] ?? []);

                if (empty($params['ids'])) {
                    return false;
                }

                unset($params['search_params']);
            }

            return $repository->getAll($params)->searchable();

        } catch (\Exception $e) {
            Log::error('Interaction message bulk searchable error error. Message - ' . $e->getMessage() , $e->getTrace());
            throw new InteractionMessageException('Interaction message bulk searchable error');
        }
    }

    /**
     * @param array $searchParams
     * @return array
     */
    private function getIds(array $searchParams): array
    {
        $searchParams['size'] = 10000;

        return array_column($this->interactionMessageRepository->search($searchParams), 'id');
    }
}
