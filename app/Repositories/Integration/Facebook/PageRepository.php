<?php

namespace App\Repositories\Integration\Facebook;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Facebook\Page;
use App\Repositories\Traits\SortTrait;

class PageRepository implements PageRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'page_title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-page_title' => [
            'field' => 'title',
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
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Page
     * 
     * @param array $params
     * @return Page
     */
    public function create($params) {
        // Active Not Set?
        if(!isset($params['is_active'])) {
            $params['is_active'] = 1;
        }

        // Page Title Exists?
        if(isset($params['page_title'])) {
            $params['title'] = $params['page_title'];
            unset($params['page_title']);
        }

        // Does User ID Already Exist?
        if(isset($params['page_id'])) {
            $page = $this->getByPageId($params['page_id']);

            // Exists?
            if(!empty($page->id)) {
                $params['id'] = $page->id;
                return $this->update($params);
            }
        }

        // Filters Cannot be null
        if(empty($params['filters'])) {
            $params['filters'] = '';
        }

        // Create Page
        return Page::create($params);
    }

    /**
     * Delete Page
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Page
        return Page::findOrFail($id)->delete();
    }

    /**
     * Get Page
     * 
     * @param array $params
     * @return Page
     */
    public function get($params) {
        // Find Page By ID
        return Page::findOrFail($params['id']);
    }

    /**
     * Get By Facebook Page ID
     * 
     * @param int $pageId
     * @return null|Page
     */
    public function getByPageId(int $pageId): ?Page {
        // Find Token By ID
        return Page::where('page_id', $pageId)->first();
    }

    /**
     * Get All Pages That Match Params
     * 
     * @param array $params
     * @return Collection<Page>
     */
    public function getAll($params) {
        $query = Page::where('dealer_id', '=', $params['dealer_id']);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
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

    /**
     * Update Page
     * 
     * @param array $params
     * @return Page
     */
    public function update($params) {
        $page = Page::findOrFail($params['id']);

        DB::transaction(function() use (&$page, $params) {
            // Page Title Exists?
            if(isset($params['page_title'])) {
                $params['title'] = $params['page_title'];
                unset($params['page_title']);
            }

            // Fill Page Details
            $page->fill($params)->save();
        });

        return $page;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
