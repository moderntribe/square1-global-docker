<?php declare(strict_types=1);

namespace App\Services\Docker\Volumes;

use App\Contracts\Volume;
use Illuminate\Support\Collection;

/**
 * A collection of all supported docker volumes.
 *
 * @package App\Services\Docker\Volumes
 */
class VolumeCollection {

	/**
	 * @var \Illuminate\Support\Collection|\App\Contracts\Volume[]
	 */
	protected Collection $volumes;

	public function __construct( Collection $volumes ) {
		$this->volumes = $volumes;
	}

	/**
	 * Return volumes supported for this operating system.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function collection(): Collection {
		return $this->volumes->filter( static function ( Volume $volume ) {
			return $volume->supported();
		} );
	}

}
