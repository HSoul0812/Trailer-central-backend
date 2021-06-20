<?php

namespace App\Services\CRM\Email\DTOs;


/**
 * Class ImapMailbox
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ImapMailbox
{
    /**
     * @var string Full Path to Folder
     */
    private $full;

    /**
     * @var string Folder Attributes
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