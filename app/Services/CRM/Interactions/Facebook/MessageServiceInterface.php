<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Support\Collection;

interface MessageServiceInterface {
    /**
     * Scrape Messages From Facebook
     * 
     * @param AccessToken $pageToken
     * @param int $pageId
     * @return Collection<Conversation>
     */
    public function scrapeMessages(AccessToken $pageToken, int $pageId): Collection;
}