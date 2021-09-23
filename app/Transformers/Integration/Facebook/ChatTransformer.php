<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Chat;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\Integration\Facebook\PageTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Transformers\User\UserTransformer;

class ChatTransformer extends TransformerAbstract
{
    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var SalesPersonTransformer
     */
    protected $salesPersonTransformer;

    /**
     * @var PageTransformer
     */
    protected $pageTransformer;

    /**
     * @var TokenTransformer
     */
    protected $tokenTransformer;

    public function __construct(
        SalesPersonTransformer $salesPerson,
        PageTransformer $page,
        TokenTransformer $token
    ) {
        $this->salesPersonTransformer = $salesPerson;
        $this->pageTransformer = $page;
        $this->tokenTransformer = $token;
    }

    public function transform(Chat $chat)
    {
        return [
            'id' => $chat->id,
            'salesPerson' => !empty($chat->salesPerson) ? $this->salesPersonTransformer->transform($chat->salesPerson) : null,
            'access_token' => $this->tokenTransformer->transform($chat->accessToken),
            'page' => $this->pageTransformer->transform($chat->page),
            'created_at' => $chat->created_at,
            'updated_at' => $chat->updated_at
        ];
    }
}
