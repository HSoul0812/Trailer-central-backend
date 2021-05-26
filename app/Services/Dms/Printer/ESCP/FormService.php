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
            chr('27') . ' ' . chr('64'),
            chr('27') . ' ' . chr('48'),
            chr('27') . ' ' . chr('108') . ' 40',
            chr('27') . ' ' . chr('81') . ' 100',
            'This is a Test Print Wow! ' . chr('13') . ' ' . chr('10'),
            chr('12'),
            chr('27') . ' ' . chr('64')
        ];
    }
}
