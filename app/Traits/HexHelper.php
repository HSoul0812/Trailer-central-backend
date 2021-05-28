<?php

namespace App\Traits;

/**
 * Trait HexHelper
 * 
 * @package App\Traits\Helpers
 */
trait HexHelper
{
    /**
     * Get Hexadecimal From Decimal
     * 
     * @param int $dec
     * @return string
     */
    public function getHex(int $dec): string
    {
        return "\x" . \dechex($dec);
    }
}