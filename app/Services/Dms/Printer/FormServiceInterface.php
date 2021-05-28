<?php

namespace App\Services\Dms\Printer;

interface FormServiceInterface {

    /**
     * Get Instructions for Form
     * 
     * @param int $formId
     * @param int $unitSaleId
     * @return array
     */
    public function getFormInstruction(int $formId, int $unitSaleId): array;
    
}
