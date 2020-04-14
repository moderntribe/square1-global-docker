<?php declare( strict_types=1 );

namespace Tribe\Sq1\Models;

/**
 * LocalDocker Model
 *
 * @package Tribe\Sq1\Models
 */
class LocalDocker {

	/**
	 * The Project's root directory
	 */
	public const CONFIG_PROJECT_ROOT   = 'project-root';

	/**
	 * The Project's name, as found in dev/docker/.projectID
	 */
	public const CONFIG_PROJECT_NAME   = 'project-name';

	/**
	 * The Project's docker directory
	 */
	public const CONFIG_DOCKER_DIR     = 'docker-dir';

	/**
	 * The Project's path to docker-compose.yml
	 */
	public const CONFIG_DOCKER_COMPOSE = 'docker-compose';

	/**
	 * The local docker's config file name
	 */
	public const CONFIG_FILE = 'sq1.yml';

}
