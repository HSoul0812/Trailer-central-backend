<?php


namespace App\Helpers;


class SanitizeHelper
{
    /**
     * @param string $string
     * @return string
     */
    public function utf8(string $string): string
    {

        $regex = <<<'END'
/
  (
	(?: [\x00-\x7F]               # single-byte sequences   0xxxxxxx
	|   [\xC0-\xDF][\x80-\xBF]    # double-byte sequences   110xxxxx 10xxxxxx
	|   [\xE0-\xEF][\x80-\xBF]{2} # triple-byte sequences   1110xxxx 10xxxxxx * 2
	|   [\xF0-\xF7][\x80-\xBF]{3} # quadruple-byte sequence 11110xxx 10xxxxxx * 3
	){1,100}                      # ...one or more times
  )
| ( [\x80-\xBF] )                 # invalid byte in range 10000000 - 10111111
| ( [\xC0-\xFF] )                 # invalid byte in range 11000000 - 11111111
/x
END;

        preg_replace_callback($regex, function ($captures) {
            if($captures[1] != "") {
                // Valid byte sequence. Return unmodified.
                return $captures[1];
            } elseif($captures[2] != "") {
                // Invalid byte of the form 10xxxxxx.
                // Encode as 11000010 10xxxxxx.
                return "\xC2" . $captures[2];
            } else {
                // Invalid byte of the form 11xxxxxx.
                // Encode as 11000011 10xxxxxx.
                return "\xC3" . chr(ord($captures[3]) - 64);
            }
        }, $string);

        return $string;
    }

    /**
     * @param array $videoEmbedCode
     * @return string
     */
    public function splitVideoEmbedCode(array $videoEmbedCode) :string
    {
        $videoEmbedCodeString = '';

        foreach($videoEmbedCode as $embed) {
            $embed = trim($embed);
            $embed = str_replace("\r", "", $embed);
            if(!empty($embed)) {
                $videoEmbedCodeString .= $embed;
                $videoEmbedCodeString .= "\n<!-- !video -->\n"; // and use this as our delimiter
            }
        }

        return $videoEmbedCodeString;
    }

    /**
     * @param string $input
     * @return string
     */
    public function removeTypographicCharacters(string $input): string
    {
        //fix for attribute No values
        if($input == '0') {
            return $input;
        }

        // http://www.toao.net/48-replacing-smart-quotes-and-em-dashes-in-mysql
        // First, replace UTF-8 characters.
        $input = str_replace(
            array(
                "\xe2\x80\x98",
                "\xe2\x80\x99",
                "\xe2\x80\x9c",
                "\xe2\x80\x9d",
                "\xe2\x80\x93",
                "\xe2\x80\x94",
                "\xe2\x80\xa6",
                "†",
                "²"
            ),
            array( "'", "'", '"', '"', '-', '--', '...', '', '' ),
            $input
        );
        // Next, replace their Windows-1252 equivalents.
        $input = str_replace(
            array( chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133) ),
            array( "'", "'", '"', '"', '-', '--', '...' ),
            $input
        );

        $input = str_replace("‘", "'", $input);
        $input = str_replace("’", "'", $input);
        $input = str_replace("”", '"', $input);
        $input = str_replace("“", '"', $input);
        $input = str_replace("–", "-", $input);
        $input = str_replace("—", "-", $input);
        $input = str_replace("…", "...", $input);

        $input = $this->normalize($input);

        $input = self::cleanForXml($input);

        return $input;
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public function superSanitize(string $string, string $delimiter = '-'): string
    {
        $string = str_replace(' ', $delimiter, $string);
        $string = str_replace('\'', '', $string);

        $ext = strrchr($string, '.');

        if ($ext !== false) {
            $string = substr($string, 0, -strlen($ext));
        }

        // Replace other special chars
        $specialCharacters = array(
            '#' => '',
            '$' => '',
            '%' => '',
            '&' => '',
            '@' => '',
            '.' => '',
            '€' => '',
            '+' => '',
            '=' => '',
            '§' => '',
            '\\' => '',
            '/' => '',
        );

        if (isset($specialCharacters[$delimiter])) {
            unset($specialCharacters[$delimiter]);
        }

        foreach($specialCharacters as $character => $replacement) {
            $string = str_replace($character, $delimiter . $replacement . $delimiter, $string);
        }

        $string = strtr($string, 'ÀÁÂÃÄÅ? áâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ', 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');

        // Remove all remaining other unknown characters
        $string = preg_replace('/[^a-zA-Z0-9\-]/', $delimiter, $string);
        $string = preg_replace('/^[\-]+/', $delimiter, $string);
        $string = preg_replace('/[\-]+$/', $delimiter, $string);
        $string = preg_replace('/[\-]{2,}/', $delimiter, $string);

        return strtolower($string);
    }

    /**
     * @param string $input
     * @return string
     */
    public function stripMultipleWhitespace(string $input): string
    {
        // replace double spaces
        $input = preg_replace("/ +/", " ", $input);
        // replace double tabs
        $input = preg_replace("/\t+/", "\t", $input);

        return $input;
    }

    /**
     * @param string $str
     * @return string
     */
    public function normalize(string $str): string
    {
        $invalid = array(
            'Š'         => 'S',
            'š'         => 's',
            'Đ'         => 'Dj',
            'đ'         => 'dj',
            'Ž'         => 'Z',
            'ž'         => 'z',
            'Č'         => 'C',
            'č'         => 'c',
            'Ć'         => 'C',
            'ć'         => 'c',
            'À'         => 'A',
            'Á'         => 'A',
            'Â'         => 'A',
            'Ã'         => 'A',
            'Ä'         => 'A',
            'Å'         => 'A',
            'Æ'         => 'A',
            'Ç'         => 'C',
            'È'         => 'E',
            'É'         => 'E',
            'Ê'         => 'E',
            'Ë'         => 'E',
            'Ì'         => 'I',
            'Í'         => 'I',
            'Î'         => 'I',
            'Ï'         => 'I',
            'Ñ'         => 'N',
            'Ò'         => 'O',
            'Ó'         => 'O',
            'Ô'         => 'O',
            'Õ'         => 'O',
            'Ö'         => 'O',
            'Ø'         => 'O',
            'Ù'         => 'U',
            'Ú'         => 'U',
            'Û'         => 'U',
            'Ü'         => 'U',
            'Ý'         => 'Y',
            'Þ'         => 'B',
            'ß'         => 'Ss',
            'à'         => 'a',
            'á'         => 'a',
            'â'         => 'a',
            'ã'         => 'a',
            'ä'         => 'a',
            'å'         => 'a',
            'æ'         => 'a',
            'ç'         => 'c',
            'è'         => 'e',
            'é'         => 'e',
            'ê'         => 'e',
            'ë'         => 'e',
            'ì'         => 'i',
            'í'         => 'i',
            'î'         => 'i',
            'ï'         => 'i',
            'ð'         => 'o',
            'ñ'         => 'n',
            'ò'         => 'o',
            'ó'         => 'o',
            'ô'         => 'o',
            'õ'         => 'o',
            'ö'         => 'o',
            'ø'         => 'o',
            'ù'         => 'u',
            'ú'         => 'u',
            'û'         => 'u',
            'ý'         => 'y',
            'ý'         => 'y',
            'þ'         => 'b',
            'ÿ'         => 'y',
            'Ŕ'         => 'R',
            'ŕ'         => 'r',
            "`"         => "'",
            "´"         => "'",
            "„"         => ",",
            "`"         => "'",
            "´"         => "'",
            "“"         => "\"",
            "”"         => "\"",
            "´"         => "'",
            "&acirc;€™" => "'",
            "{"         => "",
            "~"         => "",
            "–"         => "-",
            "’"         => "'",
            "•"         => "-"
        );

        $str = str_replace(array_keys($invalid), array_values($invalid), $str);

        return $str;
    }

    /**
     * @param string|null $value
     * @return string
     */
    public function cleanForXml(?string $value = null): string
    {
        $ret = "";
        if(empty($value)) {
            return $ret;
        }

        $length = strlen($value);

        for($i = 0; $i < $length; $i ++) {

            $current = ord($value[$i]);
            if(($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF))
            ) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }

        }

        return $ret;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function cleanFilename(string $filename):string
    {
        return preg_replace(array( '/\s/', '/\.[\.]+/', '/[^\w_\.\-]/' ), array(
            '_',
            '.',
            ''
        ), $filename);
    }

    /**
     * A simple slugify routine
     *
     * @param array $pieces
     * @return array
     */
    public function sanitizePieces(array $pieces) : array
    {
        $result = [];
        foreach($pieces as $inputStr) {
            $inputStr = str_replace(
                ' ', '-',
                preg_replace(
                    "/[^A-Za-z0-9 ]/", '',
                    strtolower($inputStr)
                )
            );

            $result[] = $inputStr;
        }

        return $result;
    }

    /**
     * @param string|null $phoneNumber
     * @return int
     */
    public function sanitizePhoneNumber(?string $phoneNumber): int
    {
        return (int)str_replace(['(', ')', '-', '+', '.'], '', $phoneNumber);
    }

    /**
     * @param string $str
     * @return string
     */
    public function removeBrokenCharacters(?string $str = null): string
    {
        if(empty($str)) {
            return '';
        }
        $sanitizedComments = html_entity_decode(mb_convert_encoding(stripslashes($str), 'HTML-ENTITIES', 'UTF-8'));
        return preg_replace('/&#(\d+);/i', '', $sanitizedComments);
    }
}
