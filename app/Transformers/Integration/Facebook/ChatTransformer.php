<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Chat;
use App\Transformers\Integration\Facebook\PageTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Transformers\User\UserTransformer;

class ChatTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [];


    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var PageTransformer
     */
    protected $pageTransformer;

    /**
     * @var TokenTransformer
     */
    protected $tokenTransformer;

    public function __construct(
        PageTransformer $page,
        TokenTransformer $token
    ) {
        $this->pageTransformer = $page;
        $this->tokenTransformer = $token;
    }

    public function transform(Chat $chat)
    {
        return [
            'id' => $chat->id,
            'access_token' => $this->tokenTransformer->transform($chat->accessToken),
            'account_name' => $chat->account_name,
            'account_id' => $chat->account_id,
            'page' => $this->pageTransformer->transform($chat->page),
            'sales_person_ids' => $chat->sales_people_ids,
            'created_at' => $chat->created_at,
            'updated_at' => $chat->updated_at
        ];
    }
}
