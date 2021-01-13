<?php

declare(strict_types=1);

namespace Tests\database\seeds;

interface SeederInterface
{
    public function seed(): void;

    public function cleanUp(): void;
}
