<?php declare( strict_types=1 );

namespace Tests\Unit\Factories;

use App\Factories\ParameterFactory;
use App\Input\ParameterManager;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\TestCase;

final class ParameterFactoryTest extends TestCase {

    public function test_it_creates_parameter_manager_instance(): void {
        $factory          = new ParameterFactory( $this->app );
        $input            = new ArrayInput( [] );
        $parameterManager = $factory->make( $input );

        $this->assertInstanceOf( ParameterManager::class, $parameterManager );
    }

}
