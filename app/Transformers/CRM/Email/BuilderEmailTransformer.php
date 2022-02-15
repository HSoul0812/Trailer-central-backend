<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use League\Fractal\TransformerAbstract;

class BuilderEmailTransformer extends TransformerAbstract {

    /**
     * Transform BuilderEmail
     * 
     * @param BuilderEmail
     * @return array
     */
    public function transform(BuilderEmail $email): array
    {
        return [
            'id' => $email->id,
            'type' => $email->type,
            'subject' => $email->subject,
            'template' => $email->template,
            'template_id' => $email->templateId,
            'user_id' => $email->userId,
            'sales_person_id' => $email->salesPersonId,
            'from_email' => $email->fromEmail
        ];
    }
}
