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
     * @param array $params
     * @throws SendInquiryFailedException
     */
    public function send($leadId, $params) {
        // Get Lead
        $lead = Lead::findOrFail($leadId);

        // Get Meta Data
        $metadata = $lead->metadata_array;

        // Set Params
        $inquiry = [
            'website' => $lead->website->domain,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'preferred_contact' => $lead->preferred_contact,
            'lead_email' => $lead->email_address,
            'comments' => $lead->comments,
            'postal' => $lead->zip,
            'phone' => $lead->phone_number,
            'is_spam' => $lead->is_spam
        ];

        // Toggle Inventory Type
        switch($lead->lead_type) {
            case "inventory":
                $inquiry['stock'] = !empty($params['stock']) ? $params['stock'] : $lead->stock;
                $inquiry['inventory_url'] = $inquiry['website'] . $lead->referral;
                $inquiry['inventory_title'] = $lead->title;
            break;
            case "part":
                $inquiry['stock'] = $params['stock'];
                $inquiry['part_url'] = implode(", ", $metadata['SPAM_FAILURES']);
                $inquiry['part_title'] = $lead->title;
            break;
            case "showroomModel":
                $inquiry['showroom_url'] = implode(", ", $metadata['SPAM_FAILURES']);
                $inquiry['showroom_title'] = $lead->title;
            break;
        }

        // Set Inquiry Name/Email
        $inquiryEmail = $lead->inquiry_email;
        $inquiryName  = $lead->inquiry_name;
        if(!empty($inquiry['is_spam'])) {
            // Set Spam Data
            $inquiry['all_failures'] = implode(", ", $metadata['SPAM_FAILURES']);
            $inquiry['all_failures_total'] = count($metadata['SPAM_FAILURES']);
            $inquiry['remote_ip'] = $metadata['REMOTE_ADDR'];
            $inquiry['forwarded_for'] = $metadata['FORWARDED_FOR'];
            $inquiry['original_contact_list'] = implode('; ', $metadata['ORIGINAL_RECIPIENTS']);
            $inquiry['resend_url'] = $metadata['REMAIL_URL'];

            // Set Spam Email
            $inquiryEmail = 'josh+spam-notify@trailercentral.com';
            $inquiryName  = '';
        }

        // Try/Send Email!
        try {
            // Send Interaction Email
            Mail::to($this->getCleanTo([
                'email' => $inquiryEmail,
                'name' => $inquiryName
            ]))->send(new InquiryEmail($inquiry));
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
