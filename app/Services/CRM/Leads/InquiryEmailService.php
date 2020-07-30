<?php

namespace App\Services\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Services\CRM\Leads\InquiryEmailServiceInterface;
use App\Mail\InquiryEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;

/**
 * Class InquiryEmailService
 * 
 * @package App\Services\CRM\Leads
 */
class InquiryEmailService implements InquiryEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * Send Email for Lead
     * 
     * @param int $leadId
     * @throws SendInquiryFailedException
     */
    public function send($leadId) {
        // Get Lead
        $lead = Lead::findOrFail($leadId);

        // Set Params
        $params = [];

        // Try/Send Email!
        try {
            // Send Interaction Email
            Mail::to($this->getCleanTo([
                'email' => $lead->inquiry_email,
                'name' => $lead->inquiry_name
            ]))->send(new InquiryEmail($params));
        } catch(\Exception $ex) {
            throw new SendInquiryFailedException($ex->getMessage());
        }

        // Store Attachments
        if(isset($params['attachments'])) {
            $params['attachments'] = $this->storeAttachments($params['attachments'], $dealerId, $messageId);
        }

        // Returns Params With Attachments
        return $params;
    }
}
