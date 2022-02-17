<?php

namespace App\Services\CRM\Leads;

use App\Services\CRM\Leads\DTOs\InquiryLead;

interface InquiryServiceInterface {
    /**
     * Create Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function create(array $params): array;

    /**
     * Send Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function send(array $params): array;

    /**
     * Text Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function text(array $params): array;

    /**
     * Merge or Create Lead
     *
     * @param InquiryLead $inquiry
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function mergeOrCreate(InquiryLead $inquiry, array $params): array;
}
