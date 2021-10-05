<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Page;
use App\Transformers\User\UserTransformer;
use App\Transformers\Integration\Facebook\CatalogTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;

class PageTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'catalogs'
    ];

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var CatalogTransformer
     */
    protected $tokenTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
        $this->tokenTransformer = new TokenTransformer();
    }

    public function transform(Page $page)
    {
        return [
            'id' => $page->id,
            'dealer' => $this->userTransformer->transform($page->user),
            'fb_id' => $page->page_id,
            'title' => $page->title,
            'access_token' => !empty($page->accessToken) ? $this->tokenTransformer->transform($page->accessToken) : null,
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at
        ];
    }

    public function includeCatalogs(Page $page)
    {
        return $this->collect($page->catalogs, new CatalogTransformer());
    }
}