<?php

namespace App\Repositories\Website\Config;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfigDefault;
use Illuminate\Database\Eloquent\Collection;

class DefaultConfigRepository implements DefaultConfigRepositoryInterface {

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

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    /**
     * Get All Default Website Config
     *
     * @param array $params
     * @return Collection<WebsiteConfigDefault>
     */
    public function getAll($params) {
        $query = WebsiteConfigDefault::select('*');

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
