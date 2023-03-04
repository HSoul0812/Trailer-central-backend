<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Post;
use App\Repositories\Repository;

interface PostRepositoryInterface extends Repository {
    /**
     * Create OR Update Post
     * 
     * @param array $params
     * @return Post
     */
    public function createOrUpdate(array $params): Post;
}