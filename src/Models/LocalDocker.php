<?php declare( strict_types=1 );

namespace Tribe\Sq1\Models;

/**
 * LocalDocker Model
 *
 * @package Tribe\Sq1\Models
 */
class LocalDocker {

	/**
	 * The Project's root directory.
	 */
	public const CONFIG_PROJECT_ROOT   = 'project_root';

	/**
	 * The Project's name, as found in dev/docker/.projectID
	 */
	public const CONFIG_PROJECT_NAME   = 'project_name';

	/**
	 * The Project's docker directory.
	 */
	public const CONFIG_DOCKER_DIR     = 'docker_dir';

	/**
	 * The Project's path to docker-compose.yml.
	 */
	public const CONFIG_DOCKER_COMPOSE = 'docker_compose';

}
