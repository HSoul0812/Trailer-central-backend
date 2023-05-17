<?php

namespace App\Domains\ViewsAndImpressions\ValidationRules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

class ExistingMonthlyImpressionCountingZipFile implements Rule
{
    private FilesystemAdapter $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('monthly-inventory-impression-countings-reports');
    }

    public function passes($attribute, $value): bool
    {
        return $this->storage->exists($value);
    }

    public function message(): string
    {
        return "File :input doesn't exist in the storage.";
    }
}
