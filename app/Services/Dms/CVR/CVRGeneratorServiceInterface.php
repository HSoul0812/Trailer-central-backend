<?php

namespace App\Services\Dms\CVR;

use App\Models\CRM\Dms\UnitSale;
use App\Services\Dms\CVR\DTOs\CVRFileDTO;

interface CVRGeneratorServiceInterface 
{
     /**
      * Generates a file in CVR format based on the data of the unit sale
      * 
      * @param UnitSale $unitSale
      * @return CVRFileDTO
      */
     public function generate(UnitSale $unitSale) : CVRFileDTO;
}
