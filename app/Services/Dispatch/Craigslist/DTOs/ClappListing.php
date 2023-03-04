<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappListing
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class ClappListing
{
    use WithConstructor, WithGetter;

    /**
     * @var Draft
     */
    private $draft;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var ActivePost
     */
    private $activePost;

    /**
     * @var Transaction
     */
    private $transaction;
}