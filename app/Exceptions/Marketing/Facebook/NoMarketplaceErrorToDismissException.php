<?php

namespace App\Exceptions\Marketing\Facebook;

/**
 * Class SendInquiryFailedException
 *
 * Use this instead of \Exception to throw any kind of marketplace error is missing and cannot be dismissed exception
 *
 * @package App\Exceptions\Marketing\Facebook
 */
class NoMarketplaceErrorToDismissException extends \Exception
{

    protected $message = 'Cannot dismiss marketplace error, no error notification exists on marketplace integration!'; 

}