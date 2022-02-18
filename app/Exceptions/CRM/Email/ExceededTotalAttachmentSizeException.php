<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ExceededTotalAttachmentSizeException
 *
 * Use this instead of \Exception to throw any kind of exceeded total upload exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ExceededTotalAttachmentSizeException extends \Exception
{

    protected $message = 'Total upload size must be less than 256 MB';

}
