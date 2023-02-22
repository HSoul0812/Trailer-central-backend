<?php

namespace App\Console\Commands\Inventory\Benchmarking;

use App\Http\Clients\ElasticSearch\ElasticSearchClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use GuzzleHttp\Promise;
use DateTime;

class EmulateHighSearchRates extends Command
{
    private const ES6_WILDCARDS_REQUEST = [
        'query' => [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'isArchived' => false
                        ]
                    ],
                    [
                        'term' => [
                            'showOnWebsite' => true
                        ]
                    ],
                    [
                        'term' => [
                            'dealerId' => '6469'
                        ]
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'swiss',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*swiss*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'snowdogg',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*snowdogg*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'chainsaw',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*chainsaw*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'plow',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*plow*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'should' => [
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'zero turn',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*zero turn*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'tractor',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*tractor*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'LAWN MOWER',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*lawn mower*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'LAWN TRACTOR',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*lawn tractor*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'walk behind',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*walk behind*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'match' => [
                                                    'description.txt' => [
                                                        'query' => 'stand on',
                                                        'operator' => 'and'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'wildcard' => [
                                                    'description' => [
                                                        'value' => '*stand on*'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'term' => [
                                        'condition' => 'new'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'must_not' => [
                    [
                        'term' => [
                            'status' => 6
                        ]
                    ]
                ]
            ]
        ],
        'aggregations' => [
            'is_special' => [
                'terms' => [
                    'field' => 'isSpecial',
                    'size' => 300
                ]
            ],
            'category' => [
                'terms' => [
                    'field' => 'category',
                    'size' => 300
                ]
            ],
            'condition' => [
                'terms' => [
                    'field' => 'condition',
                    'size' => 300
                ]
            ],
            'length' => [
                'stats' => [
                    'field' => 'length'
                ]
            ],
            'length_inches' => [
                'stats' => [
                    'field' => 'lengthInches'
                ]
            ],
            'width_inches' => [
                'stats' => [
                    'field' => 'widthInches'
                ]
            ],
            'width' => [
                'stats' => [
                    'field' => 'width'
                ]
            ],
            'height' => [
                'stats' => [
                    'field' => 'height'
                ]
            ],
            'height_inches' => [
                'stats' => [
                    'field' => 'heightInches'
                ]
            ],
            'dealer_location_id' => [
                'terms' => [
                    'field' => 'dealerLocationId',
                    'size' => 300
                ]
            ],
            'pull_type' => [
                'terms' => [
                    'field' => 'pullType',
                    'size' => 300
                ]
            ],
            'stalls' => [
                'terms' => [
                    'field' => 'numStalls',
                    'size' => 300
                ]
            ],
            'livingquarters' => [
                'terms' => [
                    'field' => 'hasLq',
                    'size' => 300
                ]
            ],
            'slideouts' => [
                'terms' => [
                    'field' => 'numSlideouts',
                    'size' => 300
                ]
            ],
            'configuration' => [
                'terms' => [
                    'field' => 'loadType',
                    'size' => 300
                ]
            ],
            'midtack' => [
                'terms' => [
                    'field' => 'hasMidtack',
                    'size' => 300
                ]
            ],
            'gvwr' => [
                'stats' => [
                    'field' => 'gvwr'
                ]
            ],
            'payload_capacity' => [
                'stats' => [
                    'field' => 'payloadCapacity'
                ]
            ],
            'manufacturer' => [
                'terms' => [
                    'field' => 'manufacturer',
                    'size' => 300
                ]
            ],
            'brand' => [
                'terms' => [
                    'field' => 'brand',
                    'size' => 300
                ]
            ],
            'price' => [
                'stats' => [
                    'field' => 'basicPrice'
                ]
            ],
            'year' => [
                'terms' => [
                    'field' => 'year',
                    'size' => 300,
                    'order' => [
                        '_term' => 'desc'
                    ]
                ]
            ],
            'axles' => [
                'terms' => [
                    'field' => 'numAxles',
                    'size' => 300
                ]
            ],
            'construction' => [
                'terms' => [
                    'field' => 'frameMaterial',
                    'size' => 300
                ]
            ],
            'color' => [
                'terms' => [
                    'field' => 'color',
                    'size' => 300
                ]
            ],
            'ramps' => [
                'terms' => [
                    'field' => 'hasRamps',
                    'size' => 300
                ]
            ],
            'passengers' => [
                'stats' => [
                    'field' => 'numPassengers'
                ]
            ]
        ],
        'sort' => [
            [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].size() != 0 && doc['status'].value == params.status ? '1': '0'",
                        'params' => [
                            'status' => 1
                        ]
                    ],
                    'order' => 'desc'
                ]
            ],
            [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].size() != 0 && doc['status'].value == params.status ? '1': '0'",
                        'params' => [
                            'status' => 4
                        ]
                    ],
                    'order' => 'desc'
                ]
            ],
            [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].size() != 0 && doc['status'].value == params.status ? '1': '0'",
                        'params' => [
                            'status' => 3
                        ]
                    ],
                    'order' => 'desc'
                ]
            ],
            [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].size() != 0 && doc['status'].value == params.status ? '1': '0'",
                        'params' => [
                            'status' => 2
                        ]
                    ],
                    'order' => 'desc'
                ]
            ],
            [
                'existingPrice' => [
                    'order' => 'desc'
                ]
            ],
            [
                'title' => [
                    'order' => 'ASC'
                ]
            ]
        ],
        'stored_fields' => [
            '_source'
        ],
        'script_fields' => [
            'distance' => [
                'script' => [
                    'source' => "if(doc['location.geo'].value != null) { return doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371; } else { return 0; }",
                    'params' => [
                        'lat' => 0.0,
                        'lng' => 0.0
                    ]
                ]
            ]
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:benchmarking:emulate-high-search-rates {requests=10} {cache=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will emulate a high search rates';

    /**
     * Execute the console command.
     *
     * @throws \Exception when some unknown error has been thrown
     */
    public function handle(): void
    {
        $times = $this->argument('requests');

        $client = app(ElasticSearchClient::class);

        $requestOptions = ['json' => self::ES6_WILDCARDS_REQUEST, 'http_errors' => false];

        $uri = sprintf('%s/inventory/_search', config('elastic.client.hosts.0'));

        $this->line(sprintf(
                'It will execute a benchmark by sending <comment>%d</comment> request concurrently',
                $times
            )
        );

        $startTime = new DateTime();

        $this->line(sprintf('Started at <comment>%s</comment>', $startTime->format('H:i:s')));

        $promises = [];

        for ($i = 0; $i < $times; $i++) {
            $promises['request_'.$i] = $client->postAsync($uri, $requestOptions);
        }

        $responses = collect(Promise\Utils::settle($promises)->wait());

        $endTime = new DateTime();

        $stats = [
            'avg' => 0
        ];

        $responses->each(static function (array $data, $i) use (&$stats): void {
            /** @var Response $response */
            $response = $data['value'];
            $content = json_decode($response->getBody(), true);

            $stats['took'][] = $content['took'];
            $stats['avg'] += $content['took'];
        });

        $stats['avg'] = round($stats['avg'] / $times, 2);
        $stats['min'] = min($stats['took']);
        $stats['max'] = max($stats['took']);

        $this->line(sprintf(
                'Finished at <comment>%s</comment>, elapsed time <comment>%s</comment>',
                $endTime->format('H:i:s'),
                $startTime->diff($endTime)->format('%i minutes %s seconds')
            )
        );

        $this->line('<info>Stats</info> (milliseconds)');

        echo json_encode($stats, JSON_PRETTY_PRINT).PHP_EOL;
    }
}
