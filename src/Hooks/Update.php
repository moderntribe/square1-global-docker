<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Hooks;

use Consolidation\AnnotatedCommand\AnnotationData;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\SquareOne\Commands\UpdateCommands;

/**
 * Update Hooks
 *
 * @package Tribe\SquareOne\Hooks
 */
class Update implements ContainerAwareInterface {

	use ContainerAwareTrait;

	/**
	 * Check for Updates when commands are run
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 */
	public function check( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'self:update-check' !== $command && 'self:update' !== $command ) {
			/** @var UpdateCommands $update */
			$update = $this->container->get( UpdateCommands::class . 'Commands' );
			$update->updateCheck( [ 'show-existing' => false ] );
		}
	}

}
