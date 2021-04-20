<?php

namespace App\Services\Dms\CVR;

use App\Services\Dms\CVR\CVRGeneratorServiceInterface;
use App\Models\CRM\Dms\UnitSale;
use App\Services\Dms\CVR\DTOs\CVRFileDTO;
use Carbon\Carbon;
class CVRGeneratorService implements CVRGeneratorServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(UnitSale $unitSale): CVRFileDTO
    {
        $writer = new \XMLWriter(); 
        $writer->openMemory();
        $writer->startDocument('1.0'); 
            $writer->startElement('GEN');
                $writer->startElement('CSV');
                    
                    $writer->writeElement('Control_Number', '');
                    $this->writeDealOnContract($writer, $unitSale);
                    $writer->writeElement('VIN', $unitSale->inventory_vin);
                    
                $writer->endElement();            
            $writer->endElement();
        $writer->endDocument(); 
        $xml = $writer->flush();
        die($xml);
    }
    
    private function writeDealOnContract(\XMLWriter &$writer, UnitSale $unitSale) : void
    {
        $carbon = new Carbon($unitSale->created_at);        
        $writer->writeElement('Deal_Date_on_Contract', $carbon->format('Y-m-d'));
    }
    
    private function writeAmountFinanced(\XMLWriter &$writer, UnitSale $unitSale) : void
    {
        $writer->writeElement('Amount_Financed', '');
    }

}
