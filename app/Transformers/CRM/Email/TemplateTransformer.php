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
             'name' => $template->name ?? $template->custom_template_name,
             'key' => $template->template_key,
             'created_at' => (string)$template->date,
         ];
    }
}
