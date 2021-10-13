<?php

declare(strict_types=1);

namespace App\Traits\Repository\ElasticSearch;

use InvalidArgumentException;

trait Helpers
{
    /**
     * Make a multimatch query with a desired relevance for a one or more fields
     *
     * @param  array  $fields  e.g: 'display_name^0.4', 'first_name'
     * @param  string  $query
     * @return array
     */
    public function makeMultiMatchQueryWithRelevance(array $fields, string $query): array
    {
        $filters = [];

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

            $criteria = [
                'bool' => [
                    'should' => []
                ]
            ];
            $criteria['bool']['should'][] = [
                'match_phrase' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'boost' => Constants::EXACT_MATCH_COEFFICIENT
                    ]
                ]
            ];

            $criteria['bool']['should'][] = [
                'match' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'fuzziness' => Constants::FUZZY_THRESHOLD,
                        'max_expansions' => Constants::MAX_EXPANSIONS,
                        'prefix_length' => Constants::PREFIX_LENGTH
                    ]
                ]
            ];

            $matchConfig = [
                'match' => [
                    $fieldConfig[0] => [
                        'query' => trim($query),
                        'operator' => 'OR'
                    ]
                ]
            ];

            if (!empty($fieldConfig[1])) {
                $matchConfig['match'][$fieldConfig[0]]['boost'] = (float) $fieldConfig[1];
            }

            $criteria['bool']['should'][] = $matchConfig;

            $filters[] = $criteria;
        }

        return $filters;
    }
}
