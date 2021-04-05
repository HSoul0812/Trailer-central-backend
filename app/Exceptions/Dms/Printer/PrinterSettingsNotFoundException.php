<?php

namespace App\Exceptions\Dms\Printer;

class PrinterSettingsNotFoundException extends \Exception
{
    protected $message = 'Printer is not configured';
}
