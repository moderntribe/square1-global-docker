<?php

namespace Tests;

use App\Providers\AppServiceProvider;
use Illuminate\Contracts\Console\Kernel;

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

        $app->make(Kernel::class)->bootstrap();

        // Initialize Yaml config after the application is booted
        $provider = new AppServiceProvider( $app );
        $provider->initConfig();

        return $app;
    }
}
