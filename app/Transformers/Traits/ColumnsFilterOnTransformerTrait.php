<?php

namespace App\Transformers\Traits;

use Illuminate\Http\Request;

trait ColumnsFilterOnTransformerTrait
{
    /**
     * @param array $resultantArray
     * @param Request $request
     *
     * @return array
     */
    protected function filterTransformerByColumns(array $resultantArray, ?Request $request = null): array
    {
        $request = empty($request) ? request() : $request;

        $includeFields = $request->input('includeFields', null);

        if (!empty($includeFields) && is_string($includeFields)) {
            $fields = explode(',', $request->input('includeFields'));

            if (count($fields) > 0) {
                $filteredArray = array_intersect_key($resultantArray, array_flip($fields));

                if (count($filteredArray)) {
                    return $filteredArray;
                }

                return $resultantArray;
            }
        }

        return $resultantArray;
    }
}
