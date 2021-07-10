<?php

namespace App\Services\CRM\Email\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ConfigValidate
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ConfigValidate
{
    use WithConstructor, WithGetter;

    /**
     * @const array Valid Types
     */
    const VALID_TYPES = ['smtp', 'imap'];


    /**
     * @const string Message if Validated Successfully
     */
    const SUCCESS_MESSAGE = 'Your :type Settings have been validated successfully!';

    /**
     * @const string Message if Validation Failed
     */
    const FAILURE_MESSAGE = 'Your :type Settings aren\'t working! Please verify :type is enabled ' .
                                'on your mail account and your details are filled out correctly.';

    /**
     * @const string Message if Config Type Doesn't Exist
     */
    const INVALID_TYPE_MESSAGE = 'The Config Type :type requested does not exist.';


    /**
     * @var string Type of Config: smtp|imap
     */
    private $type;

    /**
     * @var bool Successful Validation
     */
    private $success;

    /**
     * @var Collection IMAP Folders Returned (IMAP Only)
     */
    private $folders;

    /**
     * @var string Message Explanation of Config Validation Response
     */
    private $message;


    /**
     * Get Config Validate Message
     * 
     * @return string
     */
    public function getMessage(): string
    {
        // Message Exists?
        if($this->message) {
            return $this->message;
        }

        // Get More Generic Message
        if($this->success) {
            return $this->fillType(self::SUCCESS_MESSAGE);
        } elseif(!in_array($this->type, self::VALID_TYPES)) {
            return $this->fillType(self::INVALID_TYPE_MESSAGE);
        } else {
            return $this->fillType(self::FAILURE_MESSAGE);
        }
    }


    /**
     * Fill Type in Message
     * 
     * @param string $message
     * @return string
     */
    private function fillType(string $message): string
    {
        // Replace Type in Message
        if($this->type) {
            return str_replace(':type', strtoupper($this->type), $message);
        }

        // Set Type to NULL
        return str_replace(':type', 'NULL', $message);
    }
}