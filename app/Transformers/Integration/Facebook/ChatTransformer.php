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
    protected $defaultIncludes = [];

    protected $availableIncludes = [
        'salesPerson'
    ];


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
            'access_token' => $this->tokenTransformer->transform($chat->accessToken),
            'account_name' => $chat->account_name,
            'account_id' => $chat->account_id,
            'page' => $this->pageTransformer->transform($chat->page),
            'created_at' => $chat->created_at,
            'updated_at' => $chat->updated_at
        ];
    }

    public function includeSalesPerson(Chat $chat)
    {
        if(!empty($chat->salesPerson)) {
            return $this->item($chat->salesPerson, $this->salesPersonTransformer);
        }
        return $this->null();
    }
}
