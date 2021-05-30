<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\OsSupport;

use Illuminate\Support\Collection;

/**
 * Class BaseSupport
 *
 * @package App\Services\Docker\Dns\OsSupport
 */
abstract class BaseSupport {

	/**
	 * A collection Resolver objects.
	 */
	protected ?Collection $resolvers;

	/**
	 * Whether this os has a resolver to work with.
	 *
	 * @return bool
	 */
	abstract public function supported(): bool;

	/**
	 * BaseSupport constructor.
	 *
	 * @param  \Illuminate\Support\Collection|null  $resolvers
	 */
	public function __construct( ?Collection $resolvers = null ) {
		$this->resolvers = $resolvers;
	}

	/**
	 * Return supported resolvers for this operating system.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function resolvers(): Collection {
		return $this->resolvers;
	}

}
