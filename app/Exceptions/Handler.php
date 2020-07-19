<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @var array
     */
    protected $dontReport = [
        // \Symfony\Component\Console\Exception\RuntimeException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report( Throwable $exception ) {
        parent::report( $exception );
    }

}
