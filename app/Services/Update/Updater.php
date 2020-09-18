<?php declare( strict_types=1 );

namespace App\Services\Update;

use Filebase\Database;
use Filebase\Document;
use Illuminate\Support\Facades\Http;

/**
 * Class Updater
 *
 * @package App\Services
 */
class Updater {

    public const UPDATE_URL = 'https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest';

    /**
     * The release database.
     *
     * @var \Filebase\Database
     */
    protected $db;

    /**
     * The phar installer
     *
     * @var \App\Services\Update\Installer
     */
    protected $installer;

    /**
     * Updater constructor.
     *
     * @param  \Filebase\Database              $db  The release database
     * @param  \App\Services\Update\Installer  $installer
     */
    public function __construct( Database $db, Installer $installer ) {
        $this->db        = $db;
        $this->installer = $installer;
    }

    /**
     * Get the latest release from the GitHub API
     *
     * @param  string  $token    The GitHub Token
     *
     * @param  int     $retries  The number of times to retry the request
     *
     * @return \Filebase\Document
     */
    public function getLatestReleaseFromGitHub( string $token = '', int $retries = 0 ): ?Document {

        if ( $token ) {
            $response = Http::retry( $retries, 100 )->withToken( $token )->get( self::UPDATE_URL );
        } else {
            $response = Http::retry( $retries, 100 )->get( self::UPDATE_URL );
        }

        if ( ! $response->ok() ) {
            return null;
        }

        $json = $response->json();

        if ( empty( $json['tag_name'] ) ) {
            return null;
        }

        $document           = $this->getCachedRelease();
        $document->version  = $json['tag_name'];
        $document->download = current( $json['assets'] )['browser_download_url'] ?? '404';

        return $this->saveReleaseData( $document );
    }

    /**
     * Get the cached release document.
     *
     * @return \Filebase\Document The release document
     */
    public function getCachedRelease(): Document {
        return $this->db->get( 'release' );
    }

    /**
     * Save a release document.
     *
     * @codeCoverageIgnore
     *
     * @param  \Filebase\Document  $document
     *
     * @return \Filebase\Document|null
     */
    public function saveReleaseData( Document $document ): ?Document {
        if ( $document->save() ) {
            return $document;
        }

        return null;
    }

    /**
     * Update to the latest released phar.
     *
     * @param  \Filebase\Document  $release    The release document
     * @param  string              $localFile  The path to the so binary
     *
     * @return void
     *
     * @throws \Exception
     */
    public function update( Document $release, string $localFile ): void {
        $this->installer->download( $release, $localFile );
    }

}
