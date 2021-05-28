<?php

namespace App\Helpers\Dms\Printer;

use App\Traits\HexHelper;
use App\Exceptions\Helpers\Dms\Printer\EmptyESCPCodeException;
use App\Exceptions\Helpers\Dms\Printer\InvalidFontException;

class ESCPHelper {

    use HexHelper;

    private const ESCP = "0x1B";
    private const ESCP_START = "0x40";
    private const ESCP_RESET_MARGIN = "0x4F";
    private const ESCP_SET_MARGIN = "0x69";
    private const ESCP_SPACE = "0x33";
    private const ESCP_ABS_X = "0x24";
    private const ESCP_BREAK = "0x0A";
    private const ESCP_END = "0x0C";

    private const ESCP_BOLD_ON = "0x45";
    private const ESCP_BOLD_OFF = "0x46";
    private const ESCP_FONT = "0x6B";
    private const ESCP_FONT_SIZE = "0x58";

    public const ESCP_FONT_ROMAN = "0x00";
    public const ESCP_FONT_SANS = "0x01";
    private const ESCP_FONTS = [
        self::ESCP_FONT_ROMAN,
        self::ESCP_FONT_SANS
    ];

    /**     
     * @var array
     */
    private $escpCode;


    // Initialize ESCP Code
    public function __construct() 
    {
        $this->escpCode = [];
    }


    /**
     * Returns the generated ESCP code
     * 
     * @throws EmptyESCPCodeException
     * @return array
     */
    public function getCode() : array
    {
        if(empty($this->escpCode)) {
            throw new EmptyESCPCodeException;
        }

        return $this->escpCode;
    }


    /**
     * Start ESC/P Code
     * 
     * @return void
     */
    public function startEscpCode() : void
    {
        $this->escpCode[] = $this->escp(self::ESCP_START);
    }

    /**
     * Clear Margins With ESC/P Code
     * 
     * @return void
     */
    public function clearMargins(): void
    {
        $this->escpCode[] = $this->escp(self::ESCP_RESET_MARGIN);
        $this->escpCode[] = $this->escp(self::ESCP_SET_MARGIN) . $this->getHex(0) . $this->getHex(0);
    }

    /**
     * Set Line Spacing
     * 
     * @param int $size set size n/72 inches | defaults to 7
     * @return void
     */
    public function setLineSpacing(int $size = 7): void
    {
        $this->escpCode[] = $this->escp(self::ESCP_SPACE) . $this->getHex($size);
    }

    /**
     * End ESC/P Code
     * 
     * @return void
     */
    public function endEscpCode() : void
    {
        $this->escpCode[] = self::ESCP_END;
    }


    /**
     * Set Font + Code
     * 
     * @param string $font
     * @throws InvalidFontException
     * @return void
     */
    public function setFont(string $font) : void
    {
        if (!in_array($font, self::ESCP_FONTS)) {
            throw new InvalidFontException;
        }

        $this->escpCode[] = $this->escp(self::ESCP_FONT) . $font;
    }

    /**
     * Set Font Size
     * 
     * @param int $fontSize
     * @return void
     */
    public function setFontSize(int $fontSize = 10) : void
    {
        $this->escpCode[] = $this->escp(self::ESCP_FONT_SIZE) . "0x00" . $this->getHex($fontSize * 2) . "0x00";
    }


    /**
     * Add Text
     * 
     * @param string $text
     * @param int $spaces
     * @param null|int $left
     * @return void
     */
    public function addText(string $text, int $spaces = 0, ?int $left = 0): void
    {
        // Set Absolute Position
        if($left !== null) {
            $this->setHorizontal($left);
        }

        // Add Text
        $this->escpCode[] = $this->getWhitespace($spaces) . $text;
    }

    /**
     * Make Text Bold
     * 
     * @param bool $on
     * @return void
     */
    public function makeBold(bool $on = true): void
    {
        $this->escpCode[] = $this->escp($on ? self::ESCP_BOLD_ON : self::ESCP_BOLD_OFF);
    }


    /**
     * Get X Line Breaks
     * 
     * @param int $lines
     * @return void
     */
    public function addLineBreaks(int $lines = 0): void
    {
        for($i = 0; $i < $lines; $i++) {
            $this->escpCode[] = self::ESCP_BREAK;
        }
    }


    /**
     * Prefix ESCP
     * 
     * @param stirng $code
     * @return string
     */
    private function escp(string $code): string
    {
        return self::ESCP . $code;
    }

    /**
     * Set Horizontal Absolute
     */
    private function setHorizontal(int $left = 0): void
    {
        $this->escpCode[] = $this->escp(self::ESCP_ABS_X) . $this->getHex($left);
    }

    /**
     * Get X Whitespace
     * 
     * @param int $spaces
     * @return string
     */
    private function getWhitespace(int $spaces = 0): string {
        $whitespace = '';
        if(!empty($spaces)) {
            for($i = 0; $i < $spaces; $i++) {
                $whitespace .= ' ';
            }
        }
        return $whitespace;
    }
}