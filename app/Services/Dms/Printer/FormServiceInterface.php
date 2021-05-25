<?php

namespace App\Services\Dms\Printer;

interface FormServiceInterface {
    
    /**
     * Returns a string array containing code with print instructions for the specific
     * dealer, form and printer
     * 
     * @param int $dealerId
     * @param int $formId
     * @param array $params
     * @return array<string>
     */
    public function getFormInstruction(int $dealerId, int $formId, array $params): array;
    
}
