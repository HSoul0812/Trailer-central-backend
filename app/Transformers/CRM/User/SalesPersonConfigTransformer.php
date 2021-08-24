<?php

namespace App\Transformers\CRM\User;

use App\Services\CRM\User\DTOs\SalesPersonConfig;
use App\Transformers\CRM\Email\ImapMailboxTransformer;
use App\Transformers\CRM\User\AuthTypeTransformer;
use League\Fractal\TransformerAbstract;

class SalesPersonConfigTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'authTypes',
        'folders'
    ];

    /**
     * Transform ConfigValidate
     * 
     * @param ConfigValidate $config
     * @return array
     */
    public function transform(SalesPersonConfig $config): array
    {
        return [
            'smtp_types' => $config->smtpTypes
        ];
    }

    /**
     * Transform Auth Types
     * 
     * @return array
     */
    public function includeAuthTypes(SalesPersonConfig $config) {
        if($config->authTypes) {
            return $this->collection($config->authTypes, new AuthTypeTransformer());
        }
        return $this->null();
    }

    /**
     * Transform Default Folders
     * 
     * @return array
     */
    public function includeFolders(SalesPersonConfig $config) {
        if($config->folders) {
            return $this->collection($config->folders, new ImapMailboxTransformer());
        }
        return $this->null();
    }
}

