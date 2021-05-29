<?php

namespace App\Services\Dms\Printer\ESCP;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\Printer\Form;
use App\Models\Inventory\Inventory;
use App\Services\Dms\Printer\ESCP\FormServiceInterface;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Dms\Printer\FormRepositoryInterface;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Helpers\Dms\Printer\ESCPHelper;
use Carbon\Carbon;

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
        //$this->escpHelper->addText("DR2407", 53);
        $this->escpHelper->addLineBreaks(19);

        // Dealer Name
        $this->escpHelper->addText($unitSale->dealer->name, 4);
        $this->escpHelper->addText($unitSale->inventory->dealerLocation->license_number, 44, 2);
        $this->escpHelper->addLineBreaks(10);

        // Dealer Address
        $this->escpHelper->addText($unitSale->inventory->dealerLocation->address, 4);
        $this->escpHelper->addText($unitSale->inventory->dealerLocation->city, 29);
        $this->escpHelper->addText($unitSale->inventory->dealerLocation->region, 44, 2);
        $this->escpHelper->addText($unitSale->inventory->dealerLocation->postalcode, 53, 2);
        $this->escpHelper->addLineBreaks(10);

        // Inventory to Sell
        $this->escpHelper->addText($unitSale->inventory->vin, 4);
        $this->escpHelper->addText($unitSale->inventory->year, 29);
        $this->escpHelper->addText($unitSale->inventory->manufacturer, 37);
        $this->escpHelper->addText($this->getShortBody($unitSale->inventory->construction), 44, 2);
        $this->escpHelper->addText($unitSale->inventory->model, 53, 2);
        $this->escpHelper->addLineBreaks(10);

        // Set Fuel Type
        $this->escpHelper->makeBold();
        switch($unitSale->inventory->fuel_type) {
            case "gas":
                $this->escpHelper->addText("X", 3, 4);
            break;
            case "diesel":
                $this->escpHelper->addText("X", 8, 2);
            break;
            case "electric":
                $this->escpHelper->addText("X", 14, 4);
            break;
            default:
                $this->escpHelper->addText("X", 22, 2);
            break;
        }

        // Set Condition
        $this->escpHelper->addText("X", ($unitSale->inventory->condition === Inventory::CONDITION_NEW) ? 29 : 36);
        $this->escpHelper->makeBold(false);

        // Set Price
        $this->escpHelper->addText($unitSale->inventory->price, 55, 2);
        $this->escpHelper->addLineBreaks(10);

        // Customer
        $this->escpHelper->addText($unitSale->customer->display_full_name, 13);
        $this->escpHelper->addText(Carbon::now()->format('n/j/Y'), 53);
        $this->escpHelper->addLineBreaks(31);

        // Odometer
        $this->escpHelper->addText($unitSale->inventory->mileage, 50);
        $this->escpHelper->addLineBreaks(10);
        if(!empty($unitSale->inventory->mileage)) {
            $this->escpHelper->makeBold();
            $this->escpHelper->addText("X", 4);
            $this->escpHelper->makeBold(false);
        }
        $this->escpHelper->addLineBreaks(31);
        /*$this->escpHelper->addLineBreaks(10);
        $this->escpHelper->addText("X", 4);
        $this->escpHelper->addLineBreaks(9);
        $this->escpHelper->addText("X", 4);
        $this->escpHelper->addLineBreaks(12);*/

        // Set Odometer Date
        $this->escpHelper->addText(Carbon::now()->format('n/j/Y'), 53, 2);
        $this->escpHelper->addLineBreaks(41);

        // Customer Address
        $this->escpHelper->addText($unitSale->customer->address, 4);
        $this->escpHelper->addText($unitSale->customer->city, 29);
        $this->escpHelper->addText($unitSale->customer->region, 44, 2);
        $this->escpHelper->addText($unitSale->customer->postal_code, 53, 2);

        // Bottom Row
        //$this->escpHelper->addLineBreaks(10);
        //$this->escpHelper->addText("n/a", 4);
        //$this->escpHelper->addText("05/26/2021", 44);
        //$this->escpHelper->addText("DL - 237298", 53, 2);

        // Return Result Code From Helper
        $this->escpHelper->endEscpCode();
        return $this->escpHelper->getCode();
    }


    /**
     * Get Short Body
     * 
     * @param null|string $body
     * @return string
     */
    private function getShortBody(?string $body): string {
        // Find Shortened Body!
        if(!empty(Form::SHORT_BODY[$body])) {
            return Form::SHORT_BODY[$body];
        }

        // Return Empty
        return '';
    }
}
