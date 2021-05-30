<?php declare(strict_types=1);

namespace App\Services\Settings\Groups;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Sensitive settings like API keys.
 *
 * @package App\Services\Settings\Groups
 */
class Secrets extends FlexibleDataTransferObject {

	/**
	 * The user's ngrok token for use with "so share"
	 */
	public string $ngrok_token = '';

}
