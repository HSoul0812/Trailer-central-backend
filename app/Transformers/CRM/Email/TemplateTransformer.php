<?php

namespace App\Transformers\CRM\Email;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Email\Template;

class TemplateTransformer extends TransformerAbstract
{
    public function transform(Template $template): array
    {
        return [
            'id' => (int)$template->id,
            'user_id' => (int)$template->user_id,
            'name' => $template->name,
            'custom' => $template->custom_template_name,
            'key' => $template->template_key,
            'template' => [
               'key' => $template->template,
               'name' => $template->name,
               'metadata' => $template->template_metadata,
               'json' => $template->template_json,
            ], 
            'created_at' => (string)$template->date,
        ];
    }
}
