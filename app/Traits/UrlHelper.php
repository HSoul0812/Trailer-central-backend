<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait UrlHelper
{
    public function strip($url)
    {

        $url = preg_replace("`\[.*\]`U", "", $url);
        $url = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $url);
        $url = htmlentities($url, ENT_COMPAT, 'utf-8');
        $url = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $url);
        $url = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $url);
        return strtolower(trim($url, '-'));
    }

    public function getLinkUrl($resource, $querystring)
    {
        return BASE_URL . API_VERSION . "/{$resource}/{$querystring}";
    }

    public function urlEncode($url)
    {
        return strtr(base64_encode($url), '+/=', '-_,');
    }

    public function urlDecode($url)
    {
        return base64_decode(strtr($url, '-_,', '+/='));
    }

    public function getFileUrl($file, $secure = false)
    {
        // If it's an external URL
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $file;
        }
        if (empty($file)) {
            return '';
        }

        if ($secure) {
            return 'https://distillery-trailercentral.s3.amazonaws.com' . $file;
        }

        return 'http://distillery-trailercentral.s3.amazonaws.com' . $file;
    }

    public function getSiteFileUrl($file)
    {
        return 'http://www.trailercentral.com/' . str_replace('/var/www/vhosts/trailercentral.com/html/', '', $file);
    }
}
