<?php

namespace App\Repositories\CRM\Interactions;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\RepositoryAbstract;
use App\Traits\Repository\Pagination;
use ElasticAdapter\Documents\Document;
use Illuminate\Database\Eloquent\Collection;

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
                'fields' => ['lead_first_name^1.3', 'lead_last_name^1.3', 'user_name^1.3', 'from_email', 'to_email', 'from_number', 'to_number']
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

        if ($params['lead_ids'] ?? null) {
            $search->filter('terms', ['lead_id' => $params['lead_ids']]);
        }

        if ($params['message_type'] ?? null) {
            $search->filter('term', ['message_type' => $params['message_type']]);
        }

        if (isset($params['hidden'])) {
            $search->filter('term', ['hidden' => (bool)$params['hidden']]);
        }

        if (isset($params['is_read'])) {
            $search->filter('term', ['is_read' => (bool)$params['is_read']]);
        }

        if (isset($params['unassigned'])) {
            $search->filter('term', ['unassigned' => (bool)$params['unassigned']]);
        }

        if (isset($params['dispatched'])) {
            $search->filter('exists', ['field' => 'date_sent']);
        }

        if (isset($params['sales_person_id'])) {
            if ($params['sales_person_id'] == '-1') {
                $search->mustNot('exists', ['field' => 'sales_person_ids']);
            } else {
                $search->filter('term', ['sales_person_ids' => $params['sales_person_id']]);
            }
        }

        if ($params['latest_messages'] ?? null) {
            $search->sort("date_sent", "desc");

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

        $size = $params['size'] ?? 50;
        $search->size($size);

        return $search->execute()->documents()->map(function (Document $document) {
            return $document->getContent();
        })->toArray();
    }

    /**
     * @param array $params
     * @return array
     */
    public function searchCountOf(array $params): array
    {
        $search = InteractionMessage::boolSearch();

        if (empty($params['group_by'])) {
            throw new RepositoryInvalidArgumentException('group_by has been missed. Params - ' . json_encode($params));
        }

        if ($params['dealer_id'] ?? null) {
            $search->filter('term', ['dealer_id' => $params['dealer_id']]);
        }

        if ($params['lead_id'] ?? null) {
            $search->filter('term', ['lead_id' => $params['lead_id']]);
        }

        if (isset($params['hidden'])) {
            $search->filter('term', ['hidden' => (bool)$params['hidden']]);
        }

        if (isset($params['dispatched'])) {
            $search->filter('exists', ['field' => 'date_sent']);
        }

        if (isset($params['is_read'])) {
            $search->filter('term', ['is_read' => (bool)$params['is_read']]);
        }

        $search->size(0);

        $groupBy = is_string($params['group_by']) ? $params['group_by'] . '.keyword' : $params['group_by'];

        $search->aggregateRaw([
            "grouped_by" => [
                "terms" => ["field" => $groupBy],
                "aggs" => [
                    'group_by_lead_id' => [
                        "cardinality" => ["field" => "lead_id"]
                    ]
                ]
            ]
        ]);

        $data = [];

        $result = $search->execute()->aggregations()->toArray()['grouped_by']['buckets'];

        foreach ($result as $item) {
            if (isset($params['unique_leads']) && $params['unique_leads']) {
                $data[$item['key']] = $item['group_by_lead_id']['value'];
            } else {
                $data[$item['key']] = $item['doc_count'];
            }
        }

        return $data;
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (empty($params['ids']) || !is_array($params['ids'])) {
            throw new RepositoryInvalidArgumentException('ids has been missed. Params - ' . json_encode($params));
        }

        return InteractionMessage::query()->whereIn('id', $params['ids'])->get();
    }

    /**
     * @param $params
     * @return InteractionMessage|null
     */
    public function get($params): ?InteractionMessage
    {
        if (empty($params['tb_primary_id']) || empty('tb_name')) {
            throw new RepositoryInvalidArgumentException('tb_primary_id or tb_name have been missed. Params - ' . json_encode($params));
        }

        $query = InteractionMessage::query();

        return $query->where($params)->first();
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
     */
    public function bulkUpdate(array $params): bool
    {
        if (empty($params['ids']) || !is_array($params['ids'])) {
            throw new RepositoryInvalidArgumentException('ids has been missed. Params - ' . json_encode($params));
        }

        $ids = $params['ids'];
        unset($params['ids']);

        /** @var InteractionMessage<Collection> $interactionMessages */
        $interactionMessages = InteractionMessage::query()->whereIn('id', $ids)->get();

        foreach ($interactionMessages as $interactionMessage) {
            $interactionMessage->update($params);
        }

        return true;
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
            throw new RepositoryInvalidArgumentException('tb_name or tb_primary_id has been missed. Params - ' . json_encode($params));
        }

        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::query()->where($params)->first();

        if (!$interactionMessage instanceof InteractionMessage) {
            throw new RepositoryInvalidArgumentException('Interaction message not found. Params - ' . json_encode($params));
        }

        $interactionMessage->searchable();

        return $interactionMessage;
    }
}
