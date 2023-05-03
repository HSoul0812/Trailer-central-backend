<?php

declare(strict_types=1);

namespace App\Repositories\Glossary;

use Illuminate\Database\Eloquent\Collection;

interface GlossaryRepositoryInterface
{
    public function getAll(): Collection;
}
