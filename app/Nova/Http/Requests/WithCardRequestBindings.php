<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests;

trait WithCardRequestBindings
{
    public function __construct()
    {
        $this->constructRequestBindings();
    }

    /**
     * We must register the specific request bindings for our controller here.
     */
    abstract protected function constructRequestBindings(): void;
}
