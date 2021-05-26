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
            '27 64',
            '27 48',
            '27 108 40',
            '27 81 100',
            '68 65 86 73 68 13 10',
            '12',
            '27 64'
        ];
    }
}
