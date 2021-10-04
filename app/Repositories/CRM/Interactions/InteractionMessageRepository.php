<?php

namespace App\Repositories\CRM\Interactions;

use App\Exceptions\RepositoryInvalidArgumentException;
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

    const PER_PAGE_DEFAULT = 10;

    const SORT_DIR_DEFAULT = 'desc';
    const SORT_FIELD_DEFAULT = 'date_sent';

    /**
     * @param array $params
     * @return array
     */
    public function search(array $params): array
    {
        $perPage = $params['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginationParams = [];

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

        if ($params['sort'] ?? null) {
            $sortDir = substr($params['sort'], 0, 1) === '-' ? 'asc' : 'desc';
            $sortField = str_replace('-', '', $params['sort']);
        } else {
            $sortDir = self::SORT_DIR_DEFAULT;
            $sortField = self::SORT_FIELD_DEFAULT;
        }

        if ($params['dealer_id'] ?? null) {
            $search->filter('term', ['dealer_id' => $params['dealer_id']]);
        }

        if ($params['lead_id'] ?? null) {
            $search->filter('term', ['lead_id' => $params['lead_id']]);
        }

        if ($params['message_type'] ?? null) {
            $search->filter('term', ['message_type' => $params['message_type']]);
        }

        if (isset($params['hidden'])) {
            $search->filter('term', ['hidden' => $params['hidden']]);
        }

        if (isset($params['dispatched'])) {
            $search->filter('exists', ['field' => 'date_sent']);
        }

        if ($params['latest_messages'] ?? null) {
            $search->sort('date_sent', "desc");

            $search->collapseRaw([
                "field" => "lead_id",
                "inner_hits" => [
                    "name" => "last_messages",
                    "size" => 1,
                    "sort" => [[$sortField => $sortDir]],
                ],
                "max_concurrent_group_searches" => 4
            ]);

            $search->aggregateRaw([
                "total" => [
                    "cardinality" => [
                        "field" => "lead_id"
                    ],
                ],
            ]);

            $paginationParams['aggregationTotal'] = true;

        } else {
            $search->sort($sortField, $sortDir);
        }

        if ($params['page'] ?? null) {
            $searchResult = $this->esPagination($search, $params['page'], $perPage, $paginationParams);

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

    /**
     * @param array $params
     * @return InteractionMessage
     */
    public function create($params): InteractionMessage
    {
        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::query()->create($params);
        return $interactionMessage;
    }

    /**
     * @param array $params
     * @return InteractionMessage
     */
    public function update($params): InteractionMessage
    {
        if (empty($params['id'])) {
            throw new RepositoryInvalidArgumentException('id has been missed. Params - ' . json_encode($params));
        }

        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::findOrFail($params['id']);

        $interactionMessage->fill($params)->save();

        return $interactionMessage;
    }

    /**
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function delete($params): bool
    {
        if (empty($params['tb_name']) || empty($params['tb_primary_id'])) {
            throw new RepositoryInvalidArgumentException('message_type or tb_primary_id has been missed. Params - ' . json_encode($params));
        }

        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::query()->where($params)->first();

        if (!$interactionMessage instanceof InteractionMessage) {
            throw new RepositoryInvalidArgumentException('Interaction message not found. Params - ' . json_encode($params));
        }

        return (bool)$interactionMessage->delete();
    }

    /**
     * @param array $params
     * @return InteractionMessage
     */
    public function searchable(array $params): InteractionMessage
    {
        if (empty($params['tb_name']) || empty($params['tb_primary_id'])) {
            throw new RepositoryInvalidArgumentException('message_type or tb_primary_id has been missed. Params - ' . json_encode($params));
        }

        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::query()->where($params)->first();

        if (!$interactionMessage instanceof InteractionMessage) {
            throw new RepositoryInvalidArgumentException('Interaction message not found. Params - ' . json_encode($params));
        }

        $interactionMessage->searchable();

        return  $interactionMessage;
    }
}
