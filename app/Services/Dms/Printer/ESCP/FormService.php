<?php

namespace App\Services\Dms\Printer\ESCP;

use App\Models\CRM\Dms\UnitSale;
use App\Services\Dms\Printer\ESCP\FormServiceInterface;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Dms\Printer\FormRepositoryInterface;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Helpers\Dms\Printer\ESCPHelper;
use Illuminate\Support\Facades\Log;

class FormService implements FormServiceInterface 
{
    /**
     * @var App\Repositories\Dms\QuoteRepositoryInterface
     */
    protected $quotes;

    /**
     * @var App\Repositories\Dms\Printer\FormRepositoryInterface
     */
    protected $forms;

    /**
     * @var App\Repositories\Dms\Printer\SettingsRepositoryInterface
     */
    protected $printerSettingsRepository;

    /**     
     * @var App\Helpers\Dms\Printer\ESCPHelper
     */
    protected $escpHelper;
    
    public function __construct(
        QuoteRepositoryInterface $quotes,
        FormRepositoryInterface $forms,
        SettingsRepositoryInterface $printerSettings,
        ESCPHelper $helper
    ) {
        $this->quotes = $quotes;
        $this->forms = $forms;
        $this->settings = $printerSettings;
        $this->escpHelper = $helper;
    }

    /**
     * Get Instructions for Form
     * 
     * @param int $formId
     * @param int $unitSaleId
     * @return array
     */
    public function getFormInstruction(int $formId, int $unitSaleId): array
    {
        // Get Form
        $form = $this->forms->get(['id' => $formId]);

        // Get Unit Sale
        $unitSale = $this->quotes->get(['id' => $unitSaleId]);

        // Get Form By Name
        $method = 'getForm' . $form->name;
        if(method_exists($this, $method)) {
            return $this->{$method}($unitSale);
        }

        // Return Instructions
        return [];
    }


    /**
     * Get Form DR2407 (Colorado Dealer Bill of Sale)
     * 
     * @param UnitSale $unitSale
     * @return array
     */
    private function getFormDR2407(UnitSale $unitSale): array {
        // Initialize Form Spacing
        $this->escpHelper->startEscpCode();
        $this->escpHelper->clearMargins();
        $this->escpHelper->setLineSpacing();

        // Initialize Fonts
        $this->escpHelper->setFont(ESCPHelper::ESCP_FONT_SANS);
        $this->escpHelper->setFontSize(8);

        // Previous Bill of Sale
        $this->escpHelper->addLineBreaks(10);
        $this->escpHelper->addText("DR2407", 53);
        $this->escpHelper->addLineBreaks(21);

        // Dealer Name
        $this->escpHelper->addText("Colorado Trailers Inc.", 4);
        $this->escpHelper->addText("DL - 237298", 44, 2);
        $this->escpHelper->addLineBreaks(10);

        // Address
        $this->escpHelper->addText("100 Main Street", 4);
        $this->escpHelper->addText("Springfield", 29);
        $this->escpHelper->addText("TN", 44, 2);
        $this->escpHelper->addText("29021", 53, 2);
        $this->escpHelper->addLineBreaks(10);

        // Inventory to Sell
        $this->escpHelper->addText("1HD1FMW166Y641723", 4);
        $this->escpHelper->addText("2020", 29);
        $this->escpHelper->addText("AMER", 37);
        $this->escpHelper->addText("Alum", 44, 2);
        $this->escpHelper->addText("Rebel", 53, 2);
        $this->escpHelper->addLineBreaks(10);

        // Checkboxes / Date
        $this->escpHelper->makeBold();
        $this->escpHelper->addText("X", 3, 4);
        $this->escpHelper->addText("X", 8, 2);
        $this->escpHelper->addText("X", 14, 4);
        $this->escpHelper->addText("X", 22, 2);
        $this->escpHelper->addText("X", 29);
        $this->escpHelper->addText("X", 36);
        $this->escpHelper->makeBold(false);
        $this->escpHelper->addText("19999.99", 55, 2);
        $this->escpHelper->addLineBreaks(10);

        // Customer
        $this->escpHelper->addText("Jane Doe", 13);
        $this->escpHelper->addText("05/26/2021", 53);
        $this->escpHelper->addLineBreaks(31);

        // Odometer
        $this->escpHelper->addText("90,000", 50);
        $this->escpHelper->addLineBreaks(10);
        $this->escpHelper->makeBold();
        $this->escpHelper->addText("X", 4);
        $this->escpHelper->addLineBreaks(10);
        $this->escpHelper->addText("X", 4);
        $this->escpHelper->addLineBreaks(9);
        $this->escpHelper->addText("X", 4);
        $this->escpHelper->makeBold(false);
        $this->escpHelper->addLineBreaks(12);
        $this->escpHelper->addText("05/26/2021", 53, 2);
        $this->escpHelper->addLineBreaks(41);

        // Address (Bottom)
        $this->escpHelper->addText("100 Main Street", 4);
        $this->escpHelper->addText("Springfield", 29);
        $this->escpHelper->addText("TN", 44, 2);
        $this->escpHelper->addText("29021", 53, 2);

        // Bottom Row
        //$this->escpHelper->addLineBreaks(10);
        //$this->escpHelper->addText("n/a", 4);
        //$this->escpHelper->addText("05/26/2021", 44);
        //$this->escpHelper->addText("DL - 237298", 53, 2);

        // Return Result Code From Helper
        $this->escpHelper->endEscpCode();
        return $this->escpHelper->getCode();
    }
}
