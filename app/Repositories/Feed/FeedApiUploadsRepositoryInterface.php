<?php


namespace App\Repositories\Feed;


interface FeedApiUploadsRepositoryInterface
{

    /**
     * @param $data
     * @return mixed
     */
    public function create($data);

    /**
     * @param array $data payload data
     * @param string $code import code/short name
     * @param string $key where spec
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdate($data, $code, $key);

}
