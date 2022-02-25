<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Text\Template;

class TemplateRepository implements TemplateRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'modified_at' => [
            'field' => 'modified_at',
            'direction' => 'DESC'
        ],
        '-modified_at' => [
            'field' => 'modified_at',
            'direction' => 'ASC'
        ]
    ];
    
    public function create($params) {
        DB::beginTransaction();

        try {
            // Create Template
            $template = Template::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $template;
    }

    public function delete($params) {
        // Get Template
        $template = Template::findOrFail($params['id'])->fill(['deleted' => '1']);

        // Mark as Deleted
        $template->save();

        // Return Template
        return $template;
    }

    public function get($params) {
        return Template::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Template::where('deleted', '=', 0);
        
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
        $template = Template::findOrFail($params['id']);

        DB::transaction(function() use (&$template, $params) {
            // Fill Text Details
            $template->fill($params)->save();
        });

        return $template;
    }

    /**
     * Fill Text Template Body
     * 
     * @param string $template
     * @param array $replaces
     * @return type
     */
    public function fillTemplate($template, $replaces) {
        // Add Footer
        $body = $template . Template::REPLY_STOP;

        // Loop Replacements!
        foreach($replaces as $field => $value) {
            // Replace Field
            $body = str_replace('{' . $field . '}', $value, $body);
        }

        // Return Result Template Body Text
        return $body;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

}
