<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Interaction\GetMessagesRequest;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Interactions\MessageRepositoryInterface;

/**
 * Class IntegrationController
 * @package App\Http\Controllers\v1\Integration
 */
class MessageController extends RestfulControllerV2
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['search']);

        $this->messageRepository = $messageRepository;
    }

    public function search(Request $request)
    {
        $request = new GetMessagesRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        print_r($this->messageRepository->search($request->all()));
    }
}
