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
                        'terms' => [
                            'dealerId' => [
                                '6469'
                            ]
                        ]
                    ],
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
                        'bool' => [
                            'should' => [
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'terms' => [
                                                    'condition' => [
                                                        'new'
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
                                    'bool' => [
                                        'must_not' => [
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*swiss*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*snowdogg*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*chainsaw*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*plow*'
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
                                    'bool' => [
                                        'should' => [
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*zero AND turn*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*tract\o\r*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*lawn AND mower*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*lawn AND tract\o\r*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*walk AND behind*'
                                                ]
                                            ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'description.tokens^1'
                                                    ],
                                                    'query' => '*st\a\n\d AND on*'
                                                ]
                                            ]
                                        ]
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
        'stored_fields' => [
            '_source'
        ],
        'script_fields' => [
            'distance' => [
                'script' => [
                    'source' => "if(doc['location.geo'].value != null) {
                                return doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371;
                             } else {
                                return 0;
                             }",
                    'params' => [
                        'lat' => 0,
                        'lng' => 0
                    ]
                ]
            ]
        ],
        'sort' => [
            [
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'lang' => 'painless',
                        'source' => "if (doc['status'].size() == 0) {return 0;} else if (doc['status'].value == '1') {  return 4;}  else if (doc['status'].value == '4') {  return 3;}  else if (doc['status'].value == '3') {  return 2;}  else if (doc['status'].value == '2') {  return 1;}  else {return 0;}"
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
                    'order' => 'asc'
                ]
            ]
        ],
        'aggregations' => [
            'status' => [
                'terms' => [
                    'field' => 'status',
                    'size' => 200
                ]
            ],
            'dry_weight' => [
                'stats' => [
                    'field' => 'dryWeight'
                ]
            ],
            'is_featured' => [
                'terms' => [
                    'field' => 'isFeatured',
                    'size' => 200
                ]
            ],
            'gvwr' => [
                'stats' => [
                    'field' => 'gvwr'
                ]
            ],
            'fuel_type' => [
                'terms' => [
                    'field' => 'fuelType',
                    'size' => 200
                ]
            ],
            'sleeping_capacity' => [
                'terms' => [
                    'field' => 'numSleeps',
                    'size' => 200
                ]
            ],
            'is_special' => [
                'terms' => [
                    'field' => 'isSpecial',
                    'size' => 200
                ]
            ],
            'category' => [
                'terms' => [
                    'field' => 'category',
                    'size' => 200
                ]
            ],
            'condition' => [
                'terms' => [
                    'field' => 'condition',
                    'size' => 200
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
                    'size' => 200
                ]
            ],
            'pull_type' => [
                'terms' => [
                    'field' => 'pullType',
                    'size' => 200
                ]
            ],
            'stalls' => [
                'terms' => [
                    'field' => 'numStalls',
                    'size' => 200
                ]
            ],
            'livingquarters' => [
                'terms' => [
                    'field' => 'hasLq',
                    'size' => 200
                ]
            ],
            'slideouts' => [
                'terms' => [
                    'field' => 'numSlideouts',
                    'size' => 200
                ]
            ],
            'configuration' => [
                'terms' => [
                    'field' => 'loadType',
                    'size' => 200
                ]
            ],
            'midtack' => [
                'terms' => [
                    'field' => 'hasMidtack',
                    'size' => 200
                ]
            ],
            'payload_capacity' => [
                'stats' => [
                    'field' => 'payloadCapacity'
                ]
            ],
            'mileage_miles' => [
                'stats' => [
                    'field' => 'mileageMiles'
                ]
            ],
            'mileage_kilometres' => [
                'stats' => [
                    'field' => 'mileageKilometres'
                ]
            ],
            'is_rental' => [
                'terms' => [
                    'field' => 'isRental',
                    'size' => 200
                ]
            ],
            'manufacturer' => [
                'terms' => [
                    'field' => 'manufacturer',
                    'size' => 200
                ]
            ],
            'brand' => [
                'terms' => [
                    'field' => 'brand',
                    'size' => 200
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
                    'size' => 200,
                    'order' => [
                        '_term' => 'desc'
                    ]
                ]
            ],
            'axles' => [
                'terms' => [
                    'field' => 'numAxles',
                    'size' => 200
                ]
            ],
            'construction' => [
                'terms' => [
                    'field' => 'frameMaterial',
                    'size' => 200
                ]
            ],
            'color' => [
                'terms' => [
                    'field' => 'color',
                    'size' => 200
                ]
            ],
            'ramps' => [
                'terms' => [
                    'field' => 'hasRamps',
                    'size' => 200
                ]
            ],
            'floor_plans' => [
                'terms' => [
                    'field' => 'featureList.floorPlan',
                    'size' => 200
                ]
            ],
            'passengers' => [
                'stats' => [
                    'field' => 'numPassengers'
                ]
            ]
        ],
        'from' => 0,
        'size' => 15
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

            if (isset($content['took'])) {
                $stats['took'][] = $content['took'];
                $stats['avg'] += $content['took'];
            }
        });

        $stats['request'] = count($stats['took']);
        $stats['avg'] = round($stats['avg'] / $stats['request'], 2);
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
