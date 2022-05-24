<?php

declare(strict_types=1);

namespace App\Services\SubscribeEmailSearch;

use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepositoryInterface;
use App\Mail\SubscribeEmailSearch\SubscribeEmailSearchMail;
use App\DTOs\SubscribeEmailSearch\SubscribeEmailSearchDTO;
use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SubscribeEmailSearchService implements SubscribeEmailSearchServiceInterface
{
      /**
       * @var SubscribeEmailSearchRepositoryInterface
       */
      private $subscribeEmailSearchRepository;

     public function __construct(SubscribeEmailSearchRepositoryInterface $subscribeEmailSearchRepository)
     {
       $this->subscribeEmailSearchRepository = $subscribeEmailSearchRepository;
       
     }
     
     public function send(array $params): SubscribeEmailSearch
     {
       
       $email = Mail::to([$params['email']]);

       $subscribeEmailSearchDTO = $this->fill($params);
       
       $subscribeEmailSearch = $this->subscribeEmailSearchRepository->create($params);

       $email->send(new SubscribeEmailSearchMail($subscribeEmailSearchDTO));
       
       $subscribeEmailSearch->subscribe_email_sent = Carbon::now()->setTimezone('UTC')->toDateTimeString();
       
       $subscribeEmailSearch->save();
       
       return $subscribeEmailSearch;
     }
     
     public function fill(array $params): SubscribeEmailSearchDTO
     {
       
       $params['subject'] = 'TrailerTrader.com | Your saved search on ' . Carbon::now()->format('Y-m-d H:i:s');

       $subscribeEmailSearchDTO = SubscribeEmailSearchDTO::fromData($params);
   
       return $subscribeEmailSearchDTO;
     }
}