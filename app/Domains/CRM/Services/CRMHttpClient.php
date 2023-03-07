<?php

namespace App\Domains\CRM\Services;

use GuzzleHttp\Client;

class CRMHttpClient extends Client
{
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'base_uri' => config('app.new_design_crm_url'),
        ], $config);

        parent::__construct($config);
    }
}
