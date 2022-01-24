<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\DTOs\Lead\TcApiResponseLead;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class LeadService implements LeadServiceInterface
{

    private const INQUIRY_SEND_ROUTE = 'inquiry/send/';

    public function __construct(public GuzzleHttpClient $httpClient)
    {
    }

    /**
     * @param array
     */
    public function create(array $params): TcApiResponseLead
    {
        $url = config('services.trailercentral.api') . self::INQUIRY_SEND_ROUTE;
        $lead = $this->handleHttpRequest('PUT', $url, ['query' => $params, 'headers' => ['access-token' => config('services.trailercentral.access_token')]]);

        return TcApiResponseLead::fromData($lead['data']);
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return array
     */
    #[ArrayShape([
        'data' => [[
            'id'   => 'int',
            "website_id" => 'int',
            "dealer_id" => 'int',
            "name" => 'string',
            "lead_types" => 'array',
            "email" => 'string',
            "phone" => 'string',
            "preferred_contact" => 'string',
            "address" => 'string',
            "comments" => 'string',
            "zip" => 'string',
            "note" => 'string',
            "referral" => 'string',
            "title" => 'string',
            "status" => 'string',
            "source" => 'string',
            "next_contact_date" => 'string',
            "contact_type" => 'string',
            "created_at" => 'string',
            "inventoryInterestedIn" => 'array',
        ]],
    ])]
    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}