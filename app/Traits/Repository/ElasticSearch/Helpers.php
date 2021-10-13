<?php

declare(strict_types=1);

namespace App\Traits\Repository\ElasticSearch;

use InvalidArgumentException;

trait Helpers
{
    /**
     * Make a multimatch query with a desired relevance for a one or more fields
     *
     * @param  array  $fields e.g: 'display_name^0.4', 'first_name'
     * @param  string  $query
     * @return array
     */
    public function makeMultiMatchQueryWithRelevance(array $fields, string $query): array
    {
        $criteria = [];

        foreach ($fields as $fieldWithRelevance) {
            $fieldConfig = explode('^', $fieldWithRelevance);

            if (isset($fieldConfig[1]) && ($fieldConfig[1] >= Constants::EXACT_MATCH_COEFFICIENT || $fieldConfig[1] <= 0)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'field relevance must be less than %f and greater than %f',
                        Constants::EXACT_MATCH_COEFFICIENT,
                        0
                    )
                );
            }

            $criteria[] = [
                'match_phrase' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'boost' => Constants::EXACT_MATCH_COEFFICIENT
                    ]
                ]
            ];

            $criteria[] = [
                'match' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'fuzziness' => 'AUTO',
                    ]
                ]
            ];

            $matchConfig = [
                'match' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'operator' => 'and'
                    ]
                ]
            ];

            if (!empty($fieldConfig[1])) {
                $matchConfig['match'][$fieldConfig[0]]['boost'] = $fieldConfig[1];
            }

            $criteria[] = $matchConfig;
        }

        return $criteria;
    }
}
