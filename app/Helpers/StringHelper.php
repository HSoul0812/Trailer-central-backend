<?php

namespace App\Helpers;

/**
 * Class StringHelper
 *
 * @package App\Helpers
 */
class StringHelper
{
    /**
     * @param        $string
     * @param string $delimiter
     *
     * @return string
     */
    public static function superSanitize($string, $delimiter = '-')
    {
        $string = str_replace(' ', $delimiter, $string);
        $string = str_replace('\'', '', $string);

        $ext = strrchr($string, '.');

        if ($ext !== false) {
            $string = substr($string, 0, -strlen($ext));
        }

        // Replace other special chars
        $specialCharacters = [
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
        ];

        if (isset($specialCharacters[$delimiter])) {
            unset($specialCharacters[$delimiter]);
        }

        foreach ($specialCharacters as $character => $replacement) {
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
     * @param int $length
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getRandomHex(int $length = 20): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * @param string|null $value
     *
     * @return string|null
     */
    public static function trimWhiteSpaces(?string $value = ''): ?string
    {
        if (empty($value)) {
            return null;
        }

        $text = trim($value);

        return !empty($text)
            ? preg_replace('/\s+/', ' ', $text)
            : null;
    }
}
