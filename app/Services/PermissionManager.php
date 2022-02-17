<?php declare( strict_types=1 );

namespace App\Services;

/**
 * Determine the host's user and group id.
 *
 * Only Linux host computers need to have matching container user
 * permissions in order to keep files writable by the host.
 *
 * Fake matching a macOS user's host uid/gid to the containers will
 * prevent a recursive chmod of all files/folders and improve performance
 * during container start up.
 */
class PermissionManager {

    public const DEFAULT_UID = 1000;
    public const DEFAULT_GID = 1000;

    /**
     * Whether we should force match a host's uid/gid
     * to the container's.
     *
     * @var bool
     */
    protected $forceDefault;

    /**
     * @param  \App\Services\OperatingSystem  $os
     */
    public function __construct( OperatingSystem $os ) {
        $this->forceDefault = ( $os->getFamily() !== OperatingSystem::LINUX );
    }

    /**
     * The host user's User ID.
     *
     * @return int
     */
    public function uid(): int {
        return $this->forceDefault ? self::DEFAULT_UID : getmyuid();
    }

    /**
     * The host user's Group ID.
     *
     * @return int
     */
    public function gid(): int {
        return $this->forceDefault ? self::DEFAULT_GID : getmygid();
    }

}
