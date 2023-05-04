<?php

declare(strict_types=1);

namespace App\Transformers\Page;

use League\Fractal\TransformerAbstract;

class PageTransformer extends TransformerAbstract
{
    public function transform($page): array
    {
        return [
             'id' => (int) $page->id,
             'name' => $page->name,
             'url' => $page->url,
         ];
    }
}
