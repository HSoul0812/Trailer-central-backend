<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Email\SaveNtlmFailedException;
use App\Exceptions\CRM\Email\SaveNtlmAttachmentsFailedException;
use App\Exceptions\CRM\Email\SendNtlmFailedException;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\DTOs\NtlmChangeKey;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;

use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\Request\SendItemType;
use \jamesiarmes\PhpEws\Request\CreateItemType;
use \jamesiarmes\PhpEws\Request\CreateAttachmentType;

use \jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;

use \jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use \jamesiarmes\PhpEws\Enumeration\SensitivityChoicesType;
use \jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;

use \jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use \jamesiarmes\PhpEws\Enumeration\DistinguishedPropertySetType;
use \jamesiarmes\PhpEws\Enumeration\MapiPropertyTypeType;

use \jamesiarmes\PhpEws\Type\BodyType;
use \jamesiarmes\PhpEws\Type\EmailAddressType;
use \jamesiarmes\PhpEws\Type\MessageType;
use \jamesiarmes\PhpEws\Type\SingleRecipientType;
use \jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use \jamesiarmes\PhpEws\Type\TargetFolderIdType;

use \jamesiarmes\PhpEws\Type\ExtendedPropertyType;
use \jamesiarmes\PhpEws\Type\PathToExtendedFieldType;

use \jamesiarmes\PhpEws\Type\FileAttachmentType;
use \jamesiarmes\PhpEws\Type\ItemIdType;

/**
 * Class NtlmEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class NtlmEmailService implements NtlmEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * @var App\Services\CRM\Interactions\InteractionEmailServiceInterface
     */
    protected $interactionEmail;

    /**
     * @var \jamesiarmes\PhpEws\Client
     */
    private $client;

    /**
     * Construct NTLM Client
     */
    public function __construct(InteractionEmailServiceInterface $interactionEmail) {
        // Set Interfaces
        $this->interactionEmail = $interactionEmail;
    }


    /**
     * Send NTLM Email With Params
     * 
     * @param int $dealerId
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     */
    public function send(int $dealerId, SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Get Unique Message ID
        if(empty($parsedEmail->getMessageId())) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
            $parsedEmail->setMessageId(sprintf('<%s>', $messageId));
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $parsedEmail->getMessageId()));
        }

        // Initialize Client
        $this->client = new Client($smtpConfig->getHost(), $smtpConfig->getUsername(),
                                   $smtpConfig->getPassword(), Client::VERSION_2010);

        // Create Email
        $changeKey = $this->createItem($smtpConfig, $parsedEmail);

        // Save Attachments
        if($parsedEmail->hasAttachments()) {
            $finalChangeKey = $this->appendAttachments($changeKey, $parsedEmail);
        } else {
            $finalChangeKey = $changeKey;
        }

        // Send Item
        $this->sendItem($finalChangeKey);

        // Store Attachments
        if($parsedEmail->hasAttachments()) {
            $parsedEmail->setAttachments($this->interactionEmail->storeAttachments($dealerId, $parsedEmail));
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }


    /**
     * Create Item to Send
     * 
     * @param SmtpConfig $smtpConfig
     * @return \jamesiarmes\PhpEws\Client
     */
    private function createItem(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): NtlmChangeKey {
        // Initialize NTLM Request
        $itemType = new CreateItemType();
        $itemType->Items = new NonEmptyArrayOfAllItemsType();

        // Send and Save
        $itemType->MessageDisposition = MessageDispositionType::SAVE_ONLY;

        // Create the message.
        $message = new MessageType();
        $message->Sensitivity = SensitivityChoicesType::NORMAL;
        $message->ToRecipients = new ArrayOfRecipientsType();

        // Insert Message ID as Extended Property
        $message->ExtendedProperty = new ExtendedPropertyType();
        $message->ExtendedProperty->ExtendedFieldURI = new PathToExtendedFieldType();
        $message->ExtendedProperty->ExtendedFieldURI->DistinguishedPropertySetId = DistinguishedPropertySetType::INTERNET_HEADERS;
        $message->ExtendedProperty->ExtendedFieldURI->PropertyName = 'Message-ID';
        $message->ExtendedProperty->ExtendedFieldURI->PropertyType = MapiPropertyTypeType::STRING;

        // Set the sender.
        $message->From = new SingleRecipientType();
        $message->From->Mailbox = new EmailAddressType();
        $message->From->Mailbox->EmailAddress = $smtpConfig->getUsername();

        // Add the message to the request.
        $itemType->Items->Message[] = $this->finishNtlmMessage($message, $parsedEmail);

        // Return NTLM
        return $this->saveCreateItem($itemType);
    }

    /**
     * Finish NTLM Message
     * 
     * @param MessageType $message
     * @param ParsedEmail $parsedEmail
     * @return MessageType
     */
    private function finishNtlmMessage(MessageType $message, ParsedEmail $parsedEmail): MessageType {
        // Generate Message ID
        $message->ExtendedProperty->Value = $parsedEmail->getMessageId();

        // Set the recipient.
        $recipient = new EmailAddressType();
        if(!empty($parsedEmail->getToName())) {
            $recipient->Name = $parsedEmail->getToName();
        }
        $recipient->EmailAddress = $parsedEmail->getToEmail();
        $message->ToRecipients->Mailbox[] = $recipient;

        // Set the message Subject
        $message->Subject = $parsedEmail->getSubject();

        // Set the message Body
        $message->Body = new BodyType();
        $message->Body->_ = $parsedEmail->getBody();
        if($parsedEmail->getIsHtml()) {
            $message->Body->BodyType = BodyTypeType::HTML;
        } else {
            $message->Body->BodyType = BodyTypeType::TEXT;
        }

        // Return Updated Message
        return $message;
    }

    /**
     * Save Email Via NTLM
     * 
     * @param CreateItemType $item
     * @throws SendNtlmFailedException
     * @return NtlmChangeKey
     */
    private function saveCreateItem(CreateItemType $item): NtlmChangeKey {
        // Create NTLM Item
        $response = $this->client->CreateItem($item);

        // Handle Basic Response
        $changeKey = new NtlmChangeKey();

        // Iterate over the results, printing any error messages or message ids.
        $response_messages = $response->ResponseMessages->CreateItemResponseMessage;
        foreach($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $code = $response_message->ResponseCode;
                $message = $response_message->MessageText;
                throw new SaveNtlmFailedException("Message failed to create with \"$code: $message\"");
            }

            // Iterate over the created messages, getting the id for each
            foreach ($response_message->Items->Message as $item) {
                if(!empty($item->ItemId->Id)) {
                    $changeKey->setItemId($item->ItemId->Id);
                    $changeKey->setChangeKey($item->ItemId->ChangeKey);
                    break;
                }
            }
        }

        // Return Result
        return $changeKey;
    }


    /**
     * Save Attachments to Email
     * 
     * @param NtlmChangeKey $savedItem
     * @param ParsedEmail $parsedEmail
     * @return NtlmChangeKey
     */
    private function appendAttachments(NtlmChangeKey $savedItem, ParsedEmail $parsedEmail): NtlmChangeKey {
        // Build the Request
        $request = new CreateAttachmentType();
        $request->ParentItemId = new ItemIdType();
        $request->ParentItemId->Id = $savedItem->getItemId();
        $request->Attachments = new NonEmptyArrayOfAttachmentsType();

        // Loop Attachments
        foreach($parsedEmail->getAllAttachments() as $file) {
            // Build the file attachment.
            $attachment = new FileAttachmentType();
            $attachment->IsInline = true;
            $attachment->Content = $file->getContents();
            $attachment->Name = $file->getFileName();
            $attachment->ContentId = $file->getFileName();

            // Add Attachment to Message
            $request->Attachments->FileAttachment[] = $attachment;
        }

        // Get All Saved Attachments
        return $this->saveAttachments($request, $savedItem);
    }

    /**
     * Get All Saved Attachments
     * 
     * @param CreateAttachmentType $request
     * @throws SendNtlmFailedException
     * @return Collection<NtlmChangeKey>
     */
    private function saveAttachments(CreateAttachmentType $request, NtlmChangeKey $changeKey) {
        // Create Attachment
        $response = $this->client->CreateAttachment($request);

        // Iterate over the results, get results from attachment.
        $response_messages = $response->ResponseMessages->CreateAttachmentResponseMessage;
        foreach($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $code = $response_message->ResponseCode;
                $message = $response_message->MessageText;
                throw new SaveNtlmAttachmentsFailedException("Attachment failed to create with \"$code: $message\"");
            }

            // Set the Change Key of the Final Item
            foreach($response_message->Attachments->FileAttachment as $attachment) {
                $changeKey->setItemId($attachment->AttachmentId->RootItemId);
                $changeKey->setChangeKey($attachment->AttachmentId->RootItemChangeKey);
                $changeKey->setAttachId($attachment->AttachmentId->Id);
            }
        }

        // Return Result
        return $changeKey;
    }


    /**
     * Send NTLM Item
     * 
     * @param NtlmChangeKey $changeKey
     * @return bool
     */
    private function sendItem(NtlmChangeKey $changeKey): bool {
        // Get Send Item Request
        $request = $this->prepareSendItem($changeKey);

        // Send Item
        try {
            // Create NTLM Item
            $response = $this->client->SendItem($request);

            // Get the results
            $response_messages = $response->ResponseMessages->SendItemResponseMessage;
            foreach($response_messages as $response_message) {
                // Make sure the request succeeded.
                if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                    $code = $response_message->ResponseCode;
                    $message = $response_message->MessageText;
                    throw new SendNtlmFailedException("Message failed to send with \"$code: $message\"");
                }
            }
        } catch (\Exception $e) {
            throw new SendNtlmFailedException($e->getMessage());
        }

        // Sent Successfull
        return true;
    }

    /**
     * Prepare to Send Item
     * 
     * @param NtlmChangeKey $changeKey
     * @return SendItemType
     */
    private function prepareSendItem(NtlmChangeKey $changeKey): SendItemType {
        // Build the request.
        $request = new SendItemType();
        $request->SaveItemToFolder = true;
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();

        // Add the message to the request.
        $item = new ItemIdType();
        $item->Id = $changeKey->getItemId();
        $item->ChangeKey = $changeKey->getChangeKey();
        $request->ItemIds->ItemId[] = $item;

        // Configure the folder to save the sent message to.
        $send_folder = new TargetFolderIdType();
        $send_folder->DistinguishedFolderId = new DistinguishedFolderIdType();
        $send_folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::SENT;
        $request->SavedItemFolderId = $send_folder;

        // Return Request
        return $request;
    }
}