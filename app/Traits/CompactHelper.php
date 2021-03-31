<?php


namespace App\Traits;


class CompactHelper
{
    const BASE_CHARACTERS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /* Next prime greater than 62 ^ n / 1.618033988749894848 */
    private static $golden_primes = array(
        1,
        41,
        2377,
        147299,
        9132313,
        566201239,
        35104476161,
        2176477521929
    );
    /* Ascii :                    0  9,         A  Z,         a  z     */
    /* $chars = array_merge(range(48,57), range(65,90), range(97,122)) */
    private static $chars = array(
        0  => 48,
        1  => 49,
        2  => 50,
        3  => 51,
        4  => 52,
        5  => 53,
        6  => 54,
        7  => 55,
        8  => 56,
        9  => 57,
        10 => 65,
        11 => 66,
        12 => 67,
        13 => 68,
        14 => 69,
        15 => 70,
        16 => 71,
        17 => 72,
        18 => 73,
        19 => 74,
        20 => 75,
        21 => 76,
        22 => 77,
        23 => 78,
        24 => 79,
        25 => 80,
        26 => 81,
        27 => 82,
        28 => 83,
        29 => 84,
        30 => 85,
        31 => 86,
        32 => 87,
        33 => 88,
        34 => 89,
        35 => 90,
        36 => 97,
        37 => 98,
        38 => 99,
        39 => 100,
        40 => 101,
        41 => 102,
        42 => 103,
        43 => 104,
        44 => 105,
        45 => 106,
        46 => 107,
        47 => 108,
        48 => 109,
        49 => 110,
        50 => 111,
        51 => 112,
        52 => 113,
        53 => 114,
        54 => 115,
        55 => 116,
        56 => 117,
        57 => 118,
        58 => 119,
        59 => 120,
        60 => 121,
        61 => 122
    );

//    http://stackoverflow.com/questions/959957/php-short-hash

    /*
    static function shorten($integer)
    {
        $base = self::BASE_CHARACTERS;
        $length = strlen($base);

        $out = '';
        while($integer > $length - 1)
        {
            $fmod = fmod($integer, $length);
            $out = $base[$fmod] . $out;
            $integer = floor($integer / $length);
        }

        return $base[$integer] . $out;
    }

    static function expand($string)
    {
        $base = self::BASE_CHARACTERS;
        $length = strlen($base);

        $int = 0;
        for ($j = strlen($string) - 1; $j >= 0 ; $j--) {
            $pos = strpos($base, $string[$j]);

            $int += $pos;
        }
        if (strpos($base, $string[0]) > 0 && strlen($string) > 1) {
            $int += strpos($base, $string[0]) * ($length - 1);
        }

        return $int;
    }
    */

    static function shorten($integer) {

        return self::_alpha($integer);

    }

    static function expand($string) {

        return self::_alpha($string, true);

    }

    static function hash($integer, $length = 6) {
        // http://blog.kevburnsjr.com/php-unique-hash

        $ceil  = pow(62, $length);
        $prime = self::$golden_primes[ $length ];
        $dec   = ($integer * $prime) - floor($integer * $prime / $ceil) * $ceil;
        $hash  = self::base62($dec);

        return str_pad($hash, $length, "0", STR_PAD_LEFT);
    }

    private static function base62($int) {
        $key = "";
        while($int > 0) {
            $mod = $int - (floor($int / 62) * 62);
            $key .= chr(self::$chars[ $mod ]);
            $int = floor($int / 62);
        }

        return strrev($key);
    }

    //http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/

    private static function _alpha($in, $to_num = false, $pad_up = false, $passKey = null) {

        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for($n = 0; $n < strlen($index); $n ++) {
                $i[] = substr($index, $n, 1);
            }

            $passhash = hash('sha256', $passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512', $passKey)
                : $passhash;

            for($n = 0; $n < strlen($index); $n ++) {
                $p[] = substr($passhash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);

        if($to_num) {
            // Digital number  <<--  alphabet letter code
            $in  = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for($t = 0; $t <= $len; $t ++) {
                $bcpow = bcpow($base, $len - $t);
                $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if(is_numeric($pad_up)) {
                $pad_up --;
                if($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            if(is_numeric($pad_up)) {
                $pad_up --;
                if($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for($t = floor(log($in, $base)); $t >= 0; $t --) {
                $bcp = bcpow($base, $t);
                $a   = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in  = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;

    }

    /**
     * @return string
     */
    static function getRandomString(): string
    {
       return self::hash(time()) . base_convert(rand(1, getrandmax()), 10, 36);
    }
}
