<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Template;

class TemplateTransformer extends TransformerAbstract
{
    public function transform(Template $template)
    {
	 return [
             'id' => (int)$template->id,
             'user_id' => (int)$template->user_id,
             'name' => $template->name,
             'template' => $template->template,
             'created_at' => $template->created_at,
             'updated_at' => $template->updated_at,
             'deleted' => (int)$template->deleted,
         ];
    }
}
