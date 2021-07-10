<?php

namespace App\Transformers\CRM\User;

use App\Services\CRM\User\DTOs\SalesPersonConfig;
use App\Transformers\CRM\User\AuthTypeTransformer;
use League\Fractal\TransformerAbstract;

class SalesPersonConfigTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'authTypes'
    ];

    /**
     * Transform ConfigValidate
     * 
     * @param ConfigValidate $config
     * @return array
     */
    public function transform(SalesPersonConfig $config)
    {
        return [
            'smtp_types' => $config->smtpTypes
        ];
    }

    /**
     * Transform ImapMailbox Folders
     * @return array
     */
    public function includeAuthTypes(SalesPersonConfig $config) {
        if($config->authTypes) {
            return $this->collection($config->authTypes, new AuthTypeTransformer());
        }
        return $this->null();
    }
}

