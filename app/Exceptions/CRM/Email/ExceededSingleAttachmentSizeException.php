<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ExceededSingleAttachmentSizeException
 *
 * Use this instead of \Exception to throw any kind of exceeded single upload exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ExceededSingleAttachmentSizeException extends \Exception
{

    protected $message = 'Single upload size must be less than 256 MB.';

}
