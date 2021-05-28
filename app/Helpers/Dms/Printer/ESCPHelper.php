<?php

namespace App\Helpers\Dms\Printer;

use App\Traits\HexHelper;
use App\Exceptions\Helpers\Dms\Printer\EmptyESCPCodeException;
use App\Exceptions\Helpers\Dms\Printer\InvalidFontException;

class ESCPHelper {

    private const ESCP = "\x1B";
    private const ESCP_START = self::ESCP . "\x40";
    private const ESCP_RESET_MARGIN = self::ESCP . "\x4F";
    private const ESCP_SET_MARGIN = self::ESCP . "\x69";
    private const ESCP_ABS_X = self::ESCP . "\x24";
    private const ESCP_END = "\x0C";

    private const ESCP_BOLD_ON = self::ESCP + "\x45";
    private const ESCP_BOLD_OFF = self::ESCP + "\x46";
    private const ESCP_FONT = self::ESCP . "\x6B";
    private const ESCP_FONT_SIZE = self::ESCP . "\x58";

    public const ESCP_FONT_ROMAN = "\x00";
    public const ESCP_FONT_SANS = "\x01";
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
        $this->escpCode[] = self::ESCP_START;
    }

    /**
     * Clear Margins With ESC/P Code
     * 
     * @return void
     */
    public function clearMargins(): void
    {
        $this->escpCode[] = self::ESCP_RESET_MARGIN;
        $this->escpCode[] = self::ESCP_SET_MARGIN . $this->getHex(0) . $this->getHex(0);
    }

    /**
     * Set Line Spacing
     * 
     * @param int $size set size n/72 inches | defaults to 7
     * @return void
     */
    public function setLineSpacing(int $size = 7): void
    {
        $this->escpCode[] = self::ESCP . $this->getHex($size);
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

        $this->escpCode[] = self::ESCP_FONT . $font;
    }

    /**
     * Set Font Size
     * 
     * @param int $fontSize
     * @return void
     */
    public function setFontSize(int $fontSize = 10) : void
    {
        $this->escpCode[] = self::ESCP_FONT_SIZE . "\x00" . $this->getHex($fontSize * 2) . "\x00";
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
        $this->escpCode[] = $on ? self::ESCP_BOLD_ON : self::ESCP_BOLD_OFF;
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
            $this->escpCode[] = "\s0A";
        }
    }



    /**
     * Set Horizontal Absolute
     */
    private function setHorizontal(int $left = 0): void
    {
        $this->escpCode[] = self::ESCP . $left;
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