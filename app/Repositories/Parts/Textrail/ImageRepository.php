<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageRepository implements ImageRepositoryInterface
{
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

    public function firstOrCreate($params, $fileName, $imageData) {
        Storage::disk('s3')->put($fileName, $imageData, 'public');
        $s3ImageUrl = Storage::disk('s3')->url($fileName);
        $params['image_url'] = $s3ImageUrl;
  
        return $this->model->firstOrCreate($params);
    }

}