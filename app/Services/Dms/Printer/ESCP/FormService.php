<?php

namespace App\Services\Dms\Printer\ESCP;

use App\Services\Dms\Printer\ESCP\FormServiceInterface;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Helpers\Dms\Printer\ESCPHelper;

class FormService implements FormServiceInterface 
{
    /**     
     * @var  App\Repositories\Dms\Printer\SettingsRepositoryInterface
     */
    protected $printerSettingsRepository;
    
    /**     
     * @var App\Helpers\Dms\Printer\ZPLHelper
     */
    protected $escpHelper;
    
    public function __construct(SettingsRepositoryInterface $printerSettingsRepository)
    {
        $this->printerSettingsRepository = $printerSettingsRepository;
        $this->escpHelper = new ESCPHelper;
    }
    
    public function getFormInstruction(int $dealerId, int $formId, array $params): array
    {
        return [
            '\x1B' . '\x31',
            '\x1B' . '\x30',
            '\x1B' . '\x6C' . ' 40',
            '\x1B' . '\x51' . ' 90',
            'This is a Test Print Wow!' . '\x0A',
        ];
    }
}
