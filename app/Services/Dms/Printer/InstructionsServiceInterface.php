<?php

namespace App\Services\Dms\Printer;

interface InstructionsServiceInterface {
    
    /**
     * Returns a string containing code with print instructions for the specific
     * dealer and printer
     * 
     * @param int $dealerId
     * @param string $labelText
     * @param string $barcodeData
     * @return array;
     */
    public function getPrintInstruction(int $dealerId, string $labelText, string $barcodeData): array;
    
}
