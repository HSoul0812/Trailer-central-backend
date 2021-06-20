<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Email\DTOs\ConfigValidate;
use League\Fractal\TransformerAbstract;

class ConfigValidateTransformer extends TransformerAbstract
{
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
            'folders' => $validate->folders ? $this->array($validate->folders, new ImapMailboxTransformer()) : null
        ];
    }
}
