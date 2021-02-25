<?php

namespace App\Helpers;

/**
 * Class ConvertHelper
 * @package App\Helpers
 */
class ConvertHelper
{
    const DISPLAY_MODE_FEET= 'feet';
    const DISPLAY_MODE_INCHES= 'inches';
    const DISPLAY_MODE_FEET_ONLY = 'feetonly';
    const DISPLAY_MODE_INCHES_ONLY = 'inchesonly';
    const DISPLAY_MODE_FEET_INCHES_FEET_ONLY = 'feet_inches_feet_only';
    const DISPLAY_MODE_FEET_INCHES_INCHES_ONLY = 'feet_inches_inches_only';

    const MAX_FEET_LENGTH = 90;
    const MAX_FEET_WIDTH = 12;
    const MAX_FEET_HEIGHT = 20;

    const TYPES_MAX_FEET = [
        self::MAX_FEET_LENGTH,
        self::MAX_FEET_WIDTH,
        self::MAX_FEET_HEIGHT,
    ];

    /**
     * Convert Feet and Inches to Decimal
     *
     * @param string $input feet and inches to convert
     * @param string $displayMode feet|inches|feetonly|inchesonly
     * @param string $type
     * @return string for feet and inches display
     */
    public function fromFeetAndInches(string $input, string $displayMode, string $type): string
    {
        $feet = null;
        $inches = null;

        // Match Correct Format
        preg_match("/^\s*([+-]?\d+(?:\.\d+)?){1,3}('|’)\s*([+-]?\d+(?:\.\d+)?){1,2}('{2}|(\"|″))\s*$/", $input, $feetInchesMatches);
        preg_match("/^\s*([+-]?\d+(?:\.\d+)?){1,5}(('|’){2}|(\"|″))\s*$/", $input, $inchesMatches);
        preg_match("/^\s*([+-]?\d+(?:\.\d+)?){1,3}('|’)\s*$/", $input, $feetMatches);
        preg_match("/^\s*([+-]?\d+(?:\.\d+)?)\s*$/", $input, $numericMatches);

        // Get Feet/Inches From 0'0" or 0’0″
        if (isset($feetInchesMatches[1]) && isset($feetInchesMatches[2])) {
            $feet = $feetInchesMatches[1];
            $inches = $feetInchesMatches[3];
        }
        // Get Inches From 0'' or 0’’ or 0" or 0″
        elseif (isset($inchesMatches[1])) {
            $inches = $inchesMatches[1];
        }
        // Get Feet From 0' or 0’
        elseif (isset($feetMatches[1])) {
            $feet = $feetMatches[1];
        }
        // Get Feet/Inches From Numeric Value
        elseif (isset($numericMatches[1]) && isset(self::TYPES_MAX_FEET[$type])) {
            $maxFeet = self::TYPES_MAX_FEET[$type];

            if ($numericMatches[1] > $maxFeet) {
                $inches = $numericMatches[1];
            } else {
                $feet = $numericMatches[1];
            }
        }

        if ($feet !== null && $inches !== null) {
            $tmpFeet = $feet + round($inches / 12, 2);
        } elseif ($feet === null) {
            $tmpFeet = round($inches / 12, 2);
        } else {
            $tmpFeet = $feet;
        }

        if ($displayMode === self::DISPLAY_MODE_FEET_INCHES_FEET_ONLY) {
            return floor($tmpFeet);
        }

        if ($displayMode === self::DISPLAY_MODE_FEET_INCHES_INCHES_ONLY) {
            return ($tmpFeet - floor($tmpFeet)) * 12;
        }

        // Convert to Just Feet
        if($displayMode === self::DISPLAY_MODE_FEET_ONLY) {
            return $feet;
        }
        // Convert to Just Inches
        elseif($displayMode === self::DISPLAY_MODE_INCHES_ONLY) {
            return $inches;
        }
        // Convert to Feet Inches
        elseif($displayMode === self::DISPLAY_MODE_FEET) {
            // Calculate Feet From Inches
            $inFeet = round($inches / 12, 2);
            return $feet + $inFeet;
        } else {
            // Convert Value to Float From Feet
            $ftInches = ($feet * 12);
            return $ftInches + $inches;
        }
    }
}
