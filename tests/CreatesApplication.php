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

        $app->singleton('Illuminate\Contracts\Console\Kernel', 'Illuminate\Foundation\Console\Kernel');

        $app->make('Illuminate\Foundation\Console\Kernel')->bootstrap();

        return $app;
    }
}