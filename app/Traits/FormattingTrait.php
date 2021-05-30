<?php declare(strict_types=1);

namespace App\Traits;

/**
 * Trait FormattingTrait
 *
 * @package App\Traits
 */
trait FormattingTrait {

	/**
	 * Format command output.
	 *
	 * @param  string  $text  The command output.
	 *
	 * @return string The formatted command output.
	 */
	public function formatOutput( string $text ): string {
		return trim( $text );
	}

}
