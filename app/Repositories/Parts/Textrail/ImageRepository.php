<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageRepository implements ImageRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Textrail\Image
     */
    protected $model;

    public function __construct(Image $model) {
        $this->model = $model;
    }

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function firstOrCreate(array $params, string $fileName, string $imageData) : Image
    {
        Storage::disk('s3')->put($fileName, $imageData);
        $s3ImageUrl = Storage::disk('s3')->url($fileName);
        $params['image_url'] = $s3ImageUrl;
  
        return $this->model->firstOrCreate($params);
    }

}