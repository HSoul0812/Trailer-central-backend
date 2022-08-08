<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\Template;
use Illuminate\Support\Facades\DB;

class TemplateRepository implements TemplateRepositoryInterface
{
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

    public function create($params): Template
    {
        return Template::create($params);
    }

    public function delete($params)
    {
        return Template::destroy($params['id']);
    }

    public function get($params)
    {
        return Template::findOrFail($params['id']);
    }

    public function getAll($params)
    {
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

    public function update($params): Template
    {
        // Get Template
        $template = Template::findOrFail($params['id']);

        // Update Template
        DB::transaction(function () use (&$template, $params) {
            $template->fill($params)->save();
        });

        // Return Full Template
        return $template;
    }

    private function addSortQuery($query, $sort)
    {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
