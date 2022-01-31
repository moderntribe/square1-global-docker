<?php declare( strict_types=1 );

namespace App\Factories;

use App\Input\ParameterManager;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Input\InputInterface;

class ParameterFactory {

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    public function __construct( Application $app ) {
        $this->app = $app;
    }

    public function make( InputInterface $input ): ParameterManager {
        return $this->app->make( ParameterManager::class, [ 'input' => $input ] );
    }

}
