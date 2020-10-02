<?php

namespace App\Helpers;

/**
 * Class MeasurementHelper
 * @package App\Helpers
 */
class ConvertHelper
{
    /**
     * @param float $first
     * @param float $second
     * @return float
     */
    public function feetInchesToFeet(float $first, float $second): float
    {
        return $first + round($second / 12, 2);
    }

    /**
     * @param int|string $input
     * @param int|null $round
     * @param bool $returnFloat
     * @return float|int
     */
    public function toFeetDecimal($input, ?int $round = null, bool $returnFloat = true)
    {
        $input = str_replace(',', '', $input);
        $feetDec = floatval($input);

        $feetPattern   = "/(([0-9]*[.,]?[0-9]*) *)'/";
        $inchesPattern = "/(([0-9]*[.,]?[0-9]*) *)\"/";

        preg_match($feetPattern, $input, $feetMatches);
        preg_match($inchesPattern, $input, $inchesMatches);

        $feet   = 0;
        $inches = 0;
        if(count($feetMatches) > 0) {
            $feet = $feetMatches[1];
        }
        if(count($inchesMatches) > 0) {
            $inches = $inchesMatches[1];
        }

        if($feet || $inches) {
            $feetDec = 0;
        }

        if($feet) {
            $feetDec = floatval($feet);
        }
        if($inches) {
            if($inches > 12) {
                $feetDec += intval($inches / 12);
                $inches = ($inches % 12);
            }
            $feetDec += floatval($inches / 12);
        }

        if ($returnFloat) {
            $feetDec = floatval($feetDec);
        }

        if ($round !== null) {
            $feetDec = round($feetDec, $round);
        }

        return $feetDec;
    }

    /**
     * @param $input
     * @param int|null $round
     * @param bool $returnFloat
     * @return float|int
     */
    public function toPoundsDecimal($input, ?int $round = null, bool $returnFloat = true) {
        $input = str_replace(',', '', $input);
        $lbsDec = floatval($input);

        $lbsPattern = "/(([0-9]*[.,]?[0-9]*) *(lbs|lb)\.?)/i";
        $ozPattern  = "/(([0-9]*[.,]?[0-9]*) *(oz|o)\.?)/i";

        preg_match($lbsPattern, $input, $lbsMatches);
        preg_match($ozPattern, $input, $ozMatches);

        $pounds = 0;
        $ounces = 0;
        if(count($lbsMatches) > 0) {
            $pounds = $lbsMatches[2];
        }
        if(count($ozMatches) > 0) {
            $ounces = $ozMatches[1];
        }

        if($pounds || $ounces) {
            $lbsDec = 0;
        }

        if($pounds) {
            $lbsDec = floatval($pounds);
        }
        if($ounces) {
            if($ounces > 16) {
                $lbsDec += intval($ounces / 16);
                $ounces = ($ounces % 16);
            }
            $lbsDec += floatval($ounces / 16);
        }

        if ($returnFloat) {
            $lbsDec = floatval($lbsDec);
        }

        if ($round !== null) {
            $lbsDec = round($lbsDec, $round);
        }

        return $lbsDec;
    }

    /**
     * @param $price
     * @return float
     */
    public function toPrice($price): float
    {
        return floatval(trim(str_replace(',', '', $price), '$ '));
    }
}
