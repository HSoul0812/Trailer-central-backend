<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Email\DTOs\ConfigValidate;
use League\Fractal\TransformerAbstract;

class ConfigValidateTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'folders'
    ];

    /**
     * Transform ConfigValidate
     * 
     * @param ConfigValidate $validate
     * @return array
     */
    public function transform(ConfigValidate $validate)
    {
        return [
            'type' => $validate->type,
            'success' => $validate->success,
            'message' => $validate->getMessage()
        ];
    }

    /**
     * Transform ImapMailbox Folders
     * @return array
     */
    public function includeFolders(ConfigValidate $validate) {
        if($validate->folders) {
            return $this->collection($validate->folders, new ImapMailboxTransformer());
        }
        return $this->null();
    }
}

