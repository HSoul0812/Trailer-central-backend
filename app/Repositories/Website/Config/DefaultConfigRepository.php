<?php

namespace App\Repositories\Website\Config;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfigDefault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DefaultConfigRepository implements DefaultConfigRepositoryInterface
{
    /** @var string[] CDW-900 requires these variable to be hidden, then in a upcoming clean up we should remove them */
    private const VARIABLES_TO_BE_TEMPORARILY_HIDDEN = [
        '',
        'call-to-action/snippet/title',
        'call-to-action/snippet/size',
        'call-to-action/snippet/snippet-text',
        'website/use_proximity_distance_selector', // this variable should remains when we clean up (it is a upcoming feature)
        'general/mobile/enabled'
    ];

    private $sortOrders = [
        'sort_order' => [
            'field' => 'sort_order',
            'direction' => 'DESC'
        ],
        '-sort_order' => [
            'field' => 'sort_order',
            'direction' => 'ASC'
        ]
    ];

    public function create($params)
    {
        throw new NotImplementedException;
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get All Default Website Config
     *
     * @param array $params
     * @return Collection<WebsiteConfigDefault>
     */
    public function getAll($params)
    {
        /** @var Builder $query */
        $query = WebsiteConfigDefault::select('*')->whereNotIn('key', self::VARIABLES_TO_BE_TEMPORARILY_HIDDEN);

        if (!isset($params['sort'])) {
            $params['sort'] = '-sort_order';
        }

        if (isset($params['key'])) {
            $query = $query->where('key', $params['key']);
        }

        $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);

        return $query->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }
}
