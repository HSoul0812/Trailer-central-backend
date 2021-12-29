<?php

namespace App\Repositories\Integration\Facebook;

use App\Models\Integration\Facebook\Page;
use App\Repositories\Repository;

interface PageRepositoryInterface extends Repository {
    /**
     * Get By Facebook Page ID
     * 
     * @param int $pageId
     * @return null|Page
     */
    public function getByPageId(int $pageId): ?Page;
}