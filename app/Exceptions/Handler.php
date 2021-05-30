<?php declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

/**
 * Class Handler
 *
 * @codeCoverageIgnore
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var string[]
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $dontReport = [
		// \Symfony\Component\Console\Exception\RuntimeException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * @param  \Throwable  $e
	 *
	 * @throws \Throwable
	 */
	public function report( Throwable $e ): void {
		parent::report( $e );
	}

}
