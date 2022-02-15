<?php

namespace App\Services\Dms\Printer\ESCP;

use App\Services\Dms\Printer\ESCP\InstructionsServiceInterface;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Helpers\Dms\Printer\ESCPHelper;

class InstructionsService implements InstructionsServiceInterface 
{
    /**     
     * @var  App\Repositories\Dms\Printer\SettingsRepositoryInterface
     */
    protected $printerSettingsRepository;
    
    /**     
     * @var App\Helpers\Dms\Printer\ESCPHelper
     */
    protected $escpHelper;
    
    public function __construct(SettingsRepositoryInterface $printerSettingsRepository)
    {
        $this->printerSettingsRepository = $printerSettingsRepository;
        $this->escpHelper = new ESCPHelper;
    }
    
    public function getPrintInstruction(int $dealerId, string $labelText, string $barcodeData): array
    {
        $printerSettings = $this->printerSettingsRepository->getByDealerId($dealerId);
        $this->escpHelper->setFontSize($printerSettings->sku_price_font_size);
        $this->escpHelper->setLabelOrientation($printerSettings->label_orientation);
        $this->escpHelper->setLabelTextXPosition($printerSettings->sku_price_x_position);
        $this->escpHelper->setLabelTextYPosition($printerSettings->sku_price_y_position);
        $this->escpHelper->setLabelText($labelText);
        
        $this->escpHelper->setBarcodeWidth($printerSettings->barcode_width);
        $this->escpHelper->setBarcodeHeight($printerSettings->barcode_height);
        $this->escpHelper->setBarcodeXPosition($printerSettings->barcode_x_position);
        $this->escpHelper->setBarcodeYPosition($printerSettings->barcode_y_position);
        $this->escpHelper->setBarcodeData($barcodeData);
        
        return $this->escpHelper->getCode();
    }
}
