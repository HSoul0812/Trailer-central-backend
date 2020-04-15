<?php


namespace App\Repositories\Feed;


use App\Models\Feed\Uploads\FeedApiUpload;
use App\Repositories\GenericRepository;

/**
 * Class FeedApiUploadsRepository
 *
 * Repository for uploaded feed data
 *
 * @package App\Repositories\Feed
 */
class FeedApiUploadsRepository implements FeedApiUploadsRepositoryInterface, GenericRepository
{
    /**
     * @var FeedApiUpload
     */
    private $model;

    public function __construct(FeedApiUpload $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function create($data)
    {
        $model = $this->model->newInstance($data);

        return $model->save();
    }

    /**
     * @inheritDoc
     */
    public function createOrUpdate($data, $code, $key) {
        $builder = $this->model->newQuery();

        return $builder->updateOrCreate([
            'code' => $code,
            'key' => $key,
        ], $data);
    }

}
