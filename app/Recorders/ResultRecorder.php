<?php declare( strict_types=1 );

namespace App\Recorders;

use Illuminate\Support\Collection;

/**
 * Record command results in a singleton for use in other commands.
 *
 * @package App\Recorders
 */
class ResultRecorder extends Collection {}
