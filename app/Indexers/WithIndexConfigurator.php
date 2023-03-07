<?php

declare(strict_types=1);

namespace App\Indexers;

trait WithIndexConfigurator
{
    public function indexConfigurator(): ?IndexConfigurator
    {
        return null;
    }
}
