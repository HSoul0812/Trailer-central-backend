<?php

declare(strict_types=1);

namespace App\Transformers\Glossary;

use League\Fractal\TransformerAbstract;

class GlossaryTransformer extends TransformerAbstract
{
    public function transform($glossary): array
    {
        return [
             'id' => (int) $glossary->id,
             'denomination' => $glossary->denomination,
             'short_description' => $glossary->short_description,
             'long_description' => $glossary->long_description,
             'type' => $glossary->type,
         ];
    }
}
