<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class EmailDraft
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class EmailDraftAttachment
{
    use WithConstructor, WithGetter;

    private $filename;

    private $original_filename;
}