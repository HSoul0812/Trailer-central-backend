<?php

namespace App\Repositories\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\RepositoryAbstract;
use App\Traits\Repository\Pagination;
use ElasticAdapter\Documents\Document;

/**
 * Class InteractionLeadRepository
 * @package App\Repositories\CRM\Interactions
 */
class InteractionMessageRepository extends RepositoryAbstract implements InteractionMessageRepositoryInterface
{
    use Pagination;

    /**
     * @param array $params
     * @return array
     */
    public function search(array $params): array
    {
        $search = InteractionMessage::boolSearch();

        if ($params['query'] ?? null) {
            $search->must('multi_match', [
                'query' => $params['query'],
                'fuzziness' => 'AUTO',
                'fields' => ['title^1.3', 'lead_first_name^1.3', 'lead_last_name^1.3', 'text^0.5']
            ]);
        } else {
            $search->must('match_all', []);
        }

        if ($params['dealer_id'] ?? null) {
            $search->filter('term', ['dealer_id' => $params['dealer_id']]);
        }

        if ($params['message_type'] ?? null) {
            $search->filter('term', ['message_type' => $params['message_type']]);
        }

        if ($params['hidden'] ?? null) {
            $search->filter('term', ['hidden' => $params['hidden']]);
        }

        if ($params['page'] ?? null) {
            $searchResult = $this->esPaginationExecute($search, $params['page'], $params['per_page'] ?? 10);

            return $searchResult->documents()->map(function (Document $document) {
                return $document->getContent();
            })->toArray();
        }

        $size = $options['size'] ?? 50;
        $search->size($size);

        return $search->execute()->documents()->map(function (Document $document) {
            return $document->getContent();
        })->toArray();
    }
}
