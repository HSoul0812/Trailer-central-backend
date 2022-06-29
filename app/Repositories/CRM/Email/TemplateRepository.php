<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Email\Template;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;

class TemplateRepository implements TemplateRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'custom_template_name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'custom_template_name',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'date',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'date',
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
        return Template::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Template::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
