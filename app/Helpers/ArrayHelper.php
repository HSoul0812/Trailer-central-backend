<?php


namespace App\Helpers;

/**
 * Class ArrayHelper
 * @package App\Helpers
 */
class ArrayHelper
{
    /**
     * @param array $params
     * @param array $keys
     * @return array
     */
    public function deleteKeys(array $params, array $keys): array
    {
        return array_diff_key($params, array_flip($keys));
    }
}
