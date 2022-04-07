<?php

declare(strict_types=1);

namespace App\Repositories\Glossary;

use App\Http\Requests\Glossary\IndexGlossaryRequest;
use App\Models\Glossary\Glossary;
use Illuminate\Database\Eloquent\Collection;

class GlossaryRepository implements GlossaryRepositoryInterface
{
    /**
     * @var App\Models\Glossary\Glossary
     */
    protected $model;

    public function __construct(Glossary $model)
    {
        $this->model = $model;
    }

    public function getAll(array $params): Collection
    {
        return $this->model->all();
    }
}
