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
        // Get Instructions for Specific Form ID
        

        return [
            "\x1B" . "\x69" . "\x61" . "\x00", // set printer to ESC/P mode
            "\x1B" . "\x40", // clear memory buffer
            "\x1B" . "\x4F", "\x1B" . "\x69" . "\x30" . "\x30", // clear margins
            "\x1B" . "\x30", // set line feed smallest point
            "\x1B" . "\x6B" . "\x0B", "\x1B" . "\x58" . "\x00" . "\x15", // set font and font size

            "\x1B" . "\x61" . "\x30",
            "\x1B" . "\x24" . "\x00",
            "TEST LEFT ALIGN",
            "\x1B" . "\x61" . "\x32",
            "\x1B" . "\x24" . "\x00",
            "TEST RIGHT ALIGN" . "\x0A",
            /*getLineBreaks(4), "\x1B" . "\x24" . "\x00",
            getWhitespace(53) . "DR2407" . getLineBreaks(9),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "John Doe", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "DL - 237298" . getLineBreaks(5),

            "\x1B" . "\x24" . "\x00",                                                                                                                                                                            
            getWhitespace(4) . "100 Main Street", "\x1B" . "\x24" . "\x00",
            getWhitespace(29) . "Springfield", "\x1B" . "\x24" . "\x00",
            getWhitespace(44) . "TN", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "39021" . getLineBreaks(5),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "1HD1FMW166Y641723", "\x1B" . "\x24" . "\x00",
            getWhitespace(29) . "2020", "\x1B" . "\x24" . "\x00",
            getWhitespace(37) . "AMER", "\x1B" . "\x24" . "\x00",
            getWhitespace(44) . "Alum", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "Rebel/Matador/Marlin" . getLineBreaks(5),

            "\x1B" . "\x45", "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "X", "\x1B" . "\x24" . "\x00",
            getWhitespace(9) . "X", "\x1B" . "\x24" . "\x00",
            getWhitespace(15) . "X", "\x1B" . "\x24" . "\x00",
            getWhitespace(23) . "X", "\x1B" . "\x24" . "\x00",
            getWhitespace(29) . "X", "\x1B" . "\x24" . "\x02",
            getWhitespace(36) . "X", "\x1B" . "\x46", "\x1B" . "\x24" . "\x02",
            getWhitespace(55) . "19999.99" . getLineBreaks(5),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(13) . "Jane Doe", "\x1B" . "\x24" . "\x00",
            getWhitespace(53) . "05/26/2021" . getLineBreaks(14),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(50) . "90,000" . getLineBreaks(4),

            "\x1B" . "\x45", "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "X" . getLineBreaks(5), "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "X" . getLineBreaks(5), "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "X" . getLineBreaks(5), "\x1B" . "\x46", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "05/26/2021" . getLineBreaks(20),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "100 Main Street", "\x1B" . "\x24" . "\x00",
            getWhitespace(29) . "Springfield", "\x1B" . "\x24" . "\x00",
            getWhitespace(44) . "TN", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "39021" . getLineBreaks(5),

            "\x1B" . "\x24" . "\x00",
            getWhitespace(4) . "n/a", "\x1B" . "\x24" . "\x00",
            getWhitespace(44) . "05/23/2021", "\x1B" . "\x24" . "\x02",
            getWhitespace(53) . "DL - 237298",*/
            "\x0C" // <--- Tells the printer to print 
        ];
    }
}
