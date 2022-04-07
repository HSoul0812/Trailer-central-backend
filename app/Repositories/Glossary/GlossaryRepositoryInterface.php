<?php

declare(strict_types=1);

namespace App\Repositories\Glossary;

use App\Http\Requests\Glossary\IndexGlossaryRequest;
use Illuminate\Database\Eloquent\Collection;

interface GlossaryRepositoryInterface
{
    public function getAll(array $params): Collection;
}
