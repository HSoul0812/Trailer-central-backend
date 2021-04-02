<?php

namespace App\Helpers\Dms\Printer;

use App\Exceptions\Helpers\Dms\Printer\EmptyZPLCodeException;
use App\Exceptions\Helpers\Dms\Printer\EmptyFontSizeException;
use App\Exceptions\Helpers\Dms\Printer\EmptyLabelOrientationException;
use App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextException;
use App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextXPositionException;
use App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextYPositionException;
use App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeDataException;
use App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeHeightException;
use App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeWidthException;
use App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeXPositionException;
use App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeYPositionException;

class ZPLHelper {
    
    private const ZPL_START_LABEL = '^XA';
    private const ZPL_END_LABEL = '^XZ';
    private const ZPL_FONTSIZE_INSTRUCTION = '^CFA';
    private const ZPL_ORIENTATION_INSTRUCTION = '^FWR';
    private const ZPL_LABEL_LOCATION_INSTRUCTION = '^FO';
    private const ZPL_LABEL_TEXT_INSTRUCTION = '^FD';
    private const ZPL_LABEL_TEXT_END_INSTRUCTION = '^FS';
    private const ZPL_BARCODE_DIMESIONS_INSTRUCTION = '^BY';
    private const ZPL_BARCODE_POSITION_INSTRUCTION = '^BC';
    
    private const ORIENTATION_LANDSCAPE = 'landscape';
    
    /**     
     * @var array
     */
    private $zplCode;
    
    /**
     * @var string
     */
    private $fontSize;
    
    /**
     * @var string landscape|portrait
     */
    private $labelOrientation;
    
    /**
     * @var int
     */
    private $labelTextXPosition;
    
    /**
     * @var int
     */
    private $labelTextYPosition;
    
    /**
     * @var string
     */
    private $labelText;
    
    /**
     * @var int
     */
    private $barcodeWidth;
    
    /**
     * @var int
     */
    private $barcodeHeight;
    
    /**
     * @var int
     */
    private $barcodeXPosition;
    
    /**
     * @var int
     */
    private $barcodeYPosition;
    
    /**
     * @var string
     */
    private $barcodeData;
    
    public function __construct() 
    {
        $this->zplCode = [];
    }
    
    public function setFontSize(string $fontSize) : void
    {
        $this->fontSize = $fontSize;
    }
    
    public function setLabelOrientation(string $orientation) : void
    {
        $this->labelOrientation = $orientation;
    }
    
    public function setLabelTextXPosition(int $labelTextXPosition) : void
    {
        $this->labelTextXPosition = $labelTextXPosition;
    }
    
    public function setLabelTextYPosition(int $labelTextYPosition) : void
    {
        $this->labelTextYPosition = $labelTextYPosition;
    }
    
    public function setLabelText(string $labelText) : void
    {
        $this->labelText = $labelText;
    }
    
    public function setBarcodeWidth(int $barcodeWidth) : void
    {
        $this->barcodeWidth = $barcodeWidth;
    }
    
    public function setBarcodeHeight(int $barcodeHeight) : void
    {
        $this->barcodeHeight = $barcodeHeight;
    }
    
    public function setBarcodeXPosition(int $barcodeXPosition) : void
    {
        $this->barcodeXPosition = $barcodeXPosition;
    }
    
    public function setBarcodeYPosition(int $barcodeYPosition) : void
    {
        $this->barcodeYPosition = $barcodeYPosition;
    }
    
    public function setBarcodeData(string $barcodeData) : void
    {
        $this->barcodeData = $barcodeData;
    }
        
    /**
     * Returns the generated ZPL code
     * 
     * @return array ZPL code
     */
    public function getCode() : array
    {           
        $this->startZplCode();
            $this->setFontSizeCode();
            $this->setLabelOrientationCode();
            $this->setLabelCode();
            $this->setBarcodeDimensionsCode();
            $this->setLabelOrientationCode();
            $this->setBarcodePositionCode();            
        $this->endZplCode();        
        
        return $this->zplCode;
    }
    
    private function startZplCode() : void
    {
        $this->zplCode[] = self::ZPL_START_LABEL . "\n";
    }
    
    private function endZplCode() : void
    {
        $this->zplCode[] = self::ZPL_END_LABEL . "\n";
    }
    


    /**
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyFontSizeException
     */
    private function setFontSizeCode() : void
    {
        if (empty($this->fontSize)) {
            throw new EmptyFontSizeException;
        }
        
        $this->zplCode[] = self::ZPL_FONTSIZE_INSTRUCTION . ",{$this->fontSize}\n";
    }
        
    /**
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyLabelOrientationException
     */
    private function setLabelOrientationCode() : void
    {
        if (empty($this->labelOrientation)) {
            throw new EmptyLabelOrientationException;
        }
        
        if ($this->labelOrientation === self::ORIENTATION_LANDSCAPE) {
            $this->zplCode[] = self::ZPL_ORIENTATION_INSTRUCTION . "\n";
        }        
    }    
    
    /**
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextException
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextXPositionException
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyLabelTextYPositionException
     */
    private function setLabelCode() : void
    {
        if (empty($this->labelTextXPosition)) {
            throw new EmptyLabelTextXPositionException;
        }
        
        if (empty($this->labelTextYPosition)) {
            throw new EmptyLabelTextYPositionException;
        }
        
        if (empty($this->labelText)) {
            throw new EmptyLabelTextException;
        }
        
        $this->zplCode[] = self::ZPL_LABEL_LOCATION_INSTRUCTION . $this->labelTextXPosition .', '.$this->labelTextYPosition.self::ZPL_LABEL_TEXT_INSTRUCTION . $this->labelText . self::ZPL_LABEL_TEXT_END_INSTRUCTION . "\n";
    }
        
    /**
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeWidthException
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeHeightException
     */
    private function setBarcodeDimensionsCode() : void
    {
        if (empty($this->barcodeWidth)) {
            throw new EmptyBarcodeWidthException;
        }
        
        if (empty($this->barcodeHeight)) {
            throw new EmptyBarcodeHeightException;
        }
        
        $this->zplCode[] = self::ZPL_BARCODE_DIMESIONS_INSTRUCTION . $this->barcodeWidth . ',3,' . $this->barcodeHeight . "\n";
    }
    
    /**
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeXPositionException
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeYPositionException
     * @throws App\Exceptions\Helpers\Dms\Printer\EmptyBarcodeDataException
     */
    private function setBarcodePositionCode() : void
    {
        if (empty($this->barcodeXPosition)) {
            throw new EmptyBarcodeXPositionException;
        }
        
        if (empty($this->barcodeYPosition)) {
            throw new EmptyBarcodeYPositionException;
        }
        
        if (empty($this->barcodeData)) {
            throw new EmptyBarcodeDataException;
        }
        
        $this->zplCode[] = self::ZPL_LABEL_LOCATION_INSTRUCTION . $this->barcodeXPosition . ',' . $this->barcodeYPosition . self::ZPL_BARCODE_POSITION_INSTRUCTION . self::ZPL_LABEL_TEXT_INSTRUCTION . $this->barcodeData . self::ZPL_LABEL_TEXT_END_INSTRUCTION ."\n";
    }

}
