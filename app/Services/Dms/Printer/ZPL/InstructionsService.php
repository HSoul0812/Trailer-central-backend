<?php

namespace App\Services\Dms\Printer\ZPL;

use App\Services\Dms\Printer\ZPL\InstructionsServiceInterface;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Helpers\Dms\Printer\ZPLHelper;

class InstructionsService implements InstructionsServiceInterface 
{
    /**     
     * @var  App\Repositories\Dms\Printer\SettingsRepositoryInterface
     */
    protected $printerSettingsRepository;
    
    /**     
     * @var App\Helpers\Dms\Printer\ZPLHelper
     */
    protected $zplHelper;
    
    public function __construct(SettingsRepositoryInterface $printerSettingsRepository)
    {
        $this->printerSettingsRepository = $printerSettingsRepository;
        $this->zplHelper = new ZPLHelper;
    }
    
    public function getPrintInstruction(int $dealerId, string $labelText, string $barcodeData): array
    {
        $printerSettings = $this->printerSettingsRepository->getByDealerId($dealerId);
        $this->zplHelper->setFontSize($printerSettings->sku_price_font_size);
        $this->zplHelper->setLabelOrientation($printerSettings->label_orientation);
        $this->zplHelper->setLabelTextXPosition($printerSettings->sku_price_x_position);
        $this->zplHelper->setLabelTextYPosition($printerSettings->sku_price_y_position);
        $this->zplHelper->setLabelText($labelText);
        
        $this->zplHelper->setBarcodeWidth($printerSettings->barcode_width);
        $this->zplHelper->setBarcodeHeight($printerSettings->barcode_height);
        $this->zplHelper->setBarcodeXPosition($printerSettings->barcode_x_position);
        $this->zplHelper->setBarcodeYPosition($printerSettings->barcode_y_position);
        $this->zplHelper->setBarcodeData($barcodeData);
        
        return $this->zplHelper->getCode();
    }
}
