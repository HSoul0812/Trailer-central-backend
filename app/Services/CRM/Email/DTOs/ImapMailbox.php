<?php

namespace App\Services\CRM\Email\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ImapMailbox
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ImapMailbox
{
    use WithConstructor, WithGetter;

    /**
     * @const Default Folder Delimiter
     */
    const DELIMITER = '/';


    /**
     * @var string Full Path to Folder
     */
    private $full;

    /**
     * @var int Folder Attributes
     */
    private $attributes;

    /**
     * @var string Delimiter of Folder
     */
    private $delimiter;

    /**
     * @var string Name of Folder
     */
    private $name;
}