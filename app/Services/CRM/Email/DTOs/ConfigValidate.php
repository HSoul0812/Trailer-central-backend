<?php

namespace App\Services\CRM\Email\DTOs;

/**
 * Class ConfigConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ConfigValidate
{
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
}