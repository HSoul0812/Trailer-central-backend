<?php

namespace App\Services\CRM\Text;

use Twilio\Rest\Api\V2010\Account\MessageInstance;

interface InquiryTextServiceInterface
{
    /**
     * Send Text for Lead
     *
     * @param array $params
     * @throws SendInquiryFailedException
     * @return MessageInstance
     */
    public function send(array $params): MessageInstance;

    /**
     * Merge default values with request params
     *
     * @param array $params
     * @return array
     */
    public function merge(array $params): array;
}
