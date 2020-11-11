<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Page;
use App\Transformers\User\UserTransformer;
use App\Transformers\Integration\Facebook\CatalogTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;

class PageTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'accessToken'
    ];

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
    protected $pageTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
        $this->catalogTransformer = new CatalogTransformer();
    }

    public function transform(Page $page)
    {
        return [
            'id' => $page->id,
            'dealer' => $this->userTransformer->transform($page->user),
            'fb_id' => $page->page_id,
            'title' => $page->title,
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at
        ];
    }

    public function includeAccessToken(Page $page)
    {
        return $this->item($page->accessToken, new TokenTransformer(), new NoDataArraySerializer());
    }

    public function includeCatalogs(Page $page)
    {
        return $this->collect($page->catalogs, new CatalogTransformer(), new NoDataArraySerializer());
    }
}
