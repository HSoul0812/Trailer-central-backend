<?php

namespace App\Services\Marketing\Craigslist;

use App\Service\Marketing\Craigslist\DTOs\ClientValidate;
use App\Service\Marketing\Craigslist\DTOs\ClientValidate;
use App\Service\Marketing\Craigslist\DTOs\ClientMessage;
use Tightenco\Collect\Support\Collection;

interface ValidateServiceInterface {
    /**
     * Validate Provided Client
     * 
     * @param Client $client
     * @return ClientValidate
     */
    public function validate(Client $client): ClientValidate;

    /**
     * Return Status of All Clients
     * 
     * @param Collection<ClientValidate> $validation
     * @return Collection<ClientMessage>
     */
    public function status(Collection $validation): Collection;

    /**
     * Validate if Its Time to Send Message Now, If So, Send It
     * 
     * @param ClientMessage $message
     * @return bool
     */
    public function send(ClientMessage $message): bool;
}