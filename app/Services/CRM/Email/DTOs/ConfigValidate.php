<?php

namespace App\Services\CRM\Email\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ConfigConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ConfigValidate
{
    use WithConstructor, WithGetter;

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