<?php

namespace App\Domains\Parts\Actions;

class GetCriteriaToSearchPartInEsAction
{
    /**
     * Get the criteria to search parts in ES
     *
     * @param string $query
     * @return \array[][]
     */
    public function execute(string $query): array
    {
        return [
            [
                'function_score' => [
                    'query' => [
                        'query_string' => [
                            "query" => $query,
                            'fields' => ['title^1.3', 'part_id^3', 'sku^3', 'alternative_part_number^2'],
                        ]
                    ],
                    'boost' => 10,
                ]
            ],
            [
                'function_score' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['title^1.3', 'part_id^3', 'sku^3', 'brand', 'manufacturer', 'type', 'category', 'alternative_part_number^2', 'description^0.5'],
                            'fuzziness' => 'AUTO',
                            'operator' => 'and'
                        ],
                    ],
                    'boost' => 1,
                ]
            ]
        ];
    }
}
