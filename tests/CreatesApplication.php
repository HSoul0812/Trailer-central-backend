<?php

namespace Tests;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $kernel = $app->make(\Illuminate\Foundation\Console\Kernel::class)->bootstrap();

        return $app;
    }
}