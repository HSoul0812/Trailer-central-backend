<?php

namespace App\Traits\Marketing;

trait CraigslistHelper
{
    /*
     * Truncate By Length and Remove Special Characters
     */
    public function clTruncate($string, $length) {
        // model with & expanded
        $andCount = substr_count($string, '&');
        $result = substr($string, 0, ($length - ($andCount * 4)));
        if(empty($result)) {
            $result = '';
        }
        return $result;
    }

    /**
     * Get Tags Allowed
     */
    public function clTagsAllowed() {
        // Return Array of Allowed Tags
        return implode("", [
            '<b>', '<u>', '<i>', '<strong>', '<em>', '<hr>', '<blockquote>',
            '<p>', '<br>', '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>',
            '<font>', '<ul>', '<ol>', '<li>', '<dl>', '<dd>', '<dt>'
        ]);
    }

    /*
     * Convert Newlines to BR
     * 
     * @param string $body
     * @return string
     */
    public function clNl2br(string $body): string {
        // Handle shuffling...
        $input = str_replace("\r", "", $body);

        // Strip Out Shuffle
        if(preg_match_all('/<shuffle>(.*)<\/shuffle>/ismU', $input, $m)) {
            for($x = 0; $x < count($m[0]); $x++) {
                // replace m[0][x] with result, use m[1][x] for processing
                $shuffleLines = explode("\n", trim($m[1][$x]));
                shuffle($shuffleLines);
                $shuffleLines = implode("\n", $shuffleLines);
                $input = str_replace($m[0][$x], trim($shuffleLines), $input);
            }
        }

        // Make sure to remove any stray shuffles
        $input = str_ireplace("<shuffle>", "", $input);
        $input = str_ireplace("</shuffle>", "", $input);
        $input = str_replace("\n", "<br />\r\n", $input);

        if(mt_rand(0, 3) == 0) {
            $input .= "\r\n";
            if(mt_rand(0, 3) == 0) {
                $input .= "\r\n";
            }
        }

        return $input;
    }

    /*
     * Get Closest Color Based on CL
     * 
     * @param string $paint
     * @return string
     */
    public function clHasColor(string $paint): string {
        // Get Base Colors
        $custom = "11";
        $colors = [
            'black'  => "1",
            'blue'   => "2",
            'green'  => "3",
            'grey'   => "4",
            'gray'   => "4",
            'orange' => "5",
            'purple' => "6",
            'red'    => "7",
            'silver' => "8",
            'white'  => "9",
            'yellow' => "10",
            'brown'  => "20"
        ];

        // Loop Colors
        $chosen = 0;
        foreach($colors as $color => $key) {
            // Check if Paint is in Color
            if(strpos($paint, $color) !== false) {
                $chosen = $key;
                break;
            }
        }

        // No Color Found? Choose Custom
        if(empty($chosen)) {
            $chosen = $custom;
        }

        // Return Chosen Color ID
        return $chosen;
    }

    /*
     * Get RV Type
     * 
     * @param string $category
     * @param string $type
     * @return string
     */
    public function clFuelType(string $category, string $type = 'rv'): string {
        // Get Fuel Type
        $other = "6";
        $rvTypes = [
            'gas'      => "1",
            'diesel'   => "2",
            'hybrid'   => "3",
            'flex'     => "3",
            'electric' => "4",
            'other'    => "6",
        ];

        // Get RV Type From Category
        $chosen = $rvTypes[$category];

        // No Color Found? Choose Custom
        if(empty($chosen)) {
            $chosen = $other;
        }

        // Return Chosen Color ID
        return $chosen;
    }

    /*
     * Get RV Type
     * 
     * @param string $category
     * @return string
     */
    public function clRvType(string $category): string {
        // Get RV Type
        $other = "11";
        $rvTypes = [
            'class_a'             => "1",
            'class_b'             => "2",
            'class_c'             => "3",
            'fifth_wheel_campers' => "4",
            'camping_rv'          => "5",
            'camper_popup'        => "7",
            'toy'                 => "9",
            'truck_camper'        => "10"
        ];

        // Get RV Type From Category
        $chosen = $rvTypes[$category];

        // No Color Found? Choose Custom
        if(empty($chosen)) {
            $chosen = $other;
        }

        // Return Chosen Color ID
        return $chosen;
    }

    /*
     * Get Condition
     * 
     * @param string $condition
     * @return string
     */
    public function clCondition(string $condition): string {
        // Get RV Type
        $conditions = [
            'new'   => "10", // New
            //''    => "20", // Like New
            'used'  => "30", // Excellent
            'remfg' => "30", // Remanufactured
            //''    => "40", // Good
            //''    => "50", // Fair
            //''    => "60", // Salvage
        ];

        // Map Condition
        $chosen = $conditions[$condition];

        // No Conditon Found? Choose New
        if(empty($chosen)) {
            $chosen = $conditions['used'];
        }

        // Return Chosen Color ID
        return $chosen;
    }


    /**
     * Fix CL HTML
     * 
     * @param string $html
     * @return string
     */
    public function clFixHtml($html) {
        // Fix Italics
        $italics = preg_replace('/<(.*?)em>/i', '<$1i>', str_replace("\\", "", $html));

        // Strip Extra Special Characters
        $clean = preg_replace('/<p>\*\*<\/p><br\s+\/>/', '', $italics);

        // Return Results
        return $clean;
    }
    
    /**
     * Format Length Dimensions in Feet/Inches
     * 
     * @param int $ftput length in feet
     * @param int $input length in inches
     * @param string $mode feet|inches
     * @return string of formatted result
     */
    public function clFormatLengths(int $ftput = 0, int $input = 0, string $mode = 'feet') {
        // Initialize Feet/Inches
        $feet = 0;
        $inches = 0;
        $inFull = 0;
        $ftFull = 0;

        // Convert From Inches
        if($mode === 'inches') {
            // Convert Value to Float From Inches
            $inFull = floatval($input);
            $feet = floor($inFull / 12);
            $inches = $inFull % 12;
        } else {
            // Convert Value to Float From Feet
            $feet = intval($ftput);
            $ftFull = floatval($ftput);
            $inches = floor(($ftFull - $feet) * 12);
            $inFull = ($ftFull * 12);
        }

        // Display Only Inches
        if($mode === 'inches') {
            // Return 0 if 0
            if(empty($inFull)) {
                return 0;
            }

            // Display Inches
            return "{$inFull}\"";
        } else {
            // No Feet?
            if(empty($feet) && empty($inches)) {
                return 0;
            }

            // Inches Exist?
            if($inches > 0) {
                // Display Feet AND Inches
                return "{$feet}' {$inches}\"";
            } else {
                return "{$feet}'";
            }
        }
    }

    /**
     * Safely Encode JSON for CL
     * 
     * @param mixed $json string to parse utf8
     * @return string json result
     */
    public function clEncodeJson($json): string {
        // Json Is Array?
        if (is_array($json)) {
            foreach ($json as $k => $v) {
                $json[$k] = $this->clEncodeJson($v);
            }
        }
        else if(is_object($json)) {
            foreach ($json as $k => $v) {
                $json->$k = $this->clEncodeJson($v);
            }
        }
        elseif(is_string($json)) {
            return utf8_encode($json);
        }

        return json_encode($json);
    }
}
