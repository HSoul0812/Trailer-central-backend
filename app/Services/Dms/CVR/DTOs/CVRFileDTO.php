<?php

namespace App\Services\Dms\CVR\DTOs;

class CVRFileDTO 
{
    /**
     * @var string
     */
    private $filePath;
    
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }
    
    public function getFilePath() : string
    {
        return $this->filePath;
    }
}
