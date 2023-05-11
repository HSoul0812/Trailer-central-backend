<?php

declare(strict_types=1);

if (!function_exists('include_route_files')) {
    /**
     * Loops through a folder and requires all PHP files
     * Searches sub-directories as well.
     */
    function include_route_files($folder)
    {
        try {
            $rdi = new RecursiveDirectoryIterator($folder);
            $it = new RecursiveIteratorIterator($rdi);

            while ($it->valid()) {
                if (!$it->isDot() && $it->isFile() && $it->isReadable() && $it->current()->getExtension() === 'php') {
                    require $it->key();
                }

                $it->next();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

if (!function_exists('inject_request_data')) {
    /**
     * Injects request data from the global request.
     *
     * @param $folder
     */
    function inject_request_data(string $request_class)
    {
        return new $request_class(request()->all());
    }
}

if (!function_exists('camel_case_2_underscore')) {
    function camel_case_2_underscore($str, $separator = '_')
    {
        if (empty($str)) {
            return $str;
        }
        $str = lcfirst($str);
        $str = preg_replace('/[A-Z]/', $separator . '$0', $str);

        return strtolower($str);
    }
}
