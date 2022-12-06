<?php

namespace App\Indexers;

interface Transformer
{
    public function transform($model): array;
}
