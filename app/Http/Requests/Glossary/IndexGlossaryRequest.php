<?php

declare(strict_types=1);

namespace App\Http\Requests\Glossary;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexGlossaryRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
    ];
}
