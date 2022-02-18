<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Facebook\Image;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class ImageRepository implements ImageRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
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
     * Create Facebook Image
     * 
     * @param array $params
     * @return Image
     */
    public function create($params) {
        // Create Image
        return Image::create($params);
    }

    /**
     * Delete Image
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Image
        return Image::findOrFail($id)->delete();
    }

    /**
     * Get Image
     * 
     * @param array $params
     * @return Image
     */
    public function get($params) {
        // Find Image By ID
        return Image::findOrFail($params['id']);
    }

    /**
     * Get All Images That Match Params
     * 
     * @param array $params
     * @return Collection<Image>
     */
    public function getAll($params) {
        $query = Image::where('listing_id', '=', $params['listing_id']);

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->get();
    }

    /**
     * Update Image
     * 
     * @param array $params
     * @return Image
     */
    public function update($params) {
        $image = Image::findOrFail($params['id']);

        DB::transaction(function() use (&$image, $params) {
            // Fill Image Details
            $image->fill($params)->save();
        });

        return $image;
    }

    /**
     * Delete All Images By Listing ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteAll(int $id): bool {
        // Delete All Images By Marketplace ID
        return Image::where('listing_id', $id)->delete();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
