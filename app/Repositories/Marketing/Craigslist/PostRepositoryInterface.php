<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Post;
use App\Repositories\Repository;

interface PostRepositoryInterface extends Repository {
    /**
     * Find Post
     * 
     * @param array $params
     * @return null|Post
     */
    public function find(array $params): ?Post;

    /**
     * Create OR Update Post
     * 
     * @param array $params
     * @return Post
     */
    public function createOrUpdate(array $params): Post;
}