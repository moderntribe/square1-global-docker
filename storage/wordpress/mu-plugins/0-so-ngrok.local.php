<?php declare(strict_types=1);
/**
 * Plugin Name: SquareOne Ngrok Local Sharing
 * Plugin URI: https://tri.be
 * Description: This plugin should not be committed to git our left around.
 * Version: 1.0.0
 * Author: Justin Frydman
 * License: GPLv2+
 */

namespace Tribe\Mu;

/**
 * This is copied to a local project via the so cli.
 *
 * @package Tribe\Mu
 */
final class Ngrok {

    /**
     * @var string
     */
    private $site_url;

    /**
     * Ngrok constructor.
     *
     * @param  string  $site_url
     */
    public function __construct( string $site_url ) {
        $this->site_url = $site_url;
        $this->configure();
    }

    /**
     * Configure the plugin.
     */
    private function configure(): void {
        if ( ! defined( 'WP_SITEURL' ) && ! defined( 'WP_HOME' ) && ! empty( $_SERVER['HTTP_X_ORIGINAL_HOST'] ) ) {
            $host                 = $_SERVER['HTTP_X_ORIGINAL_HOST'];
            $_SERVER['HTTP_HOST'] = $host;
            define( 'WP_SITEURL', sprintf( 'https://%s', $host ) );
            define( 'WP_HOME', sprintf( 'https://%s', $host ) );
        }
    }

    /**
     * Replace the site url with the ngrok url.
     *
     * @param  string  $html  The buffered HTML.
     *
     * @return string The replaced HTML.
     */
    public function replace( string $html ): string {
        return str_replace( $this->site_url, wp_make_link_relative( $this->site_url ), $html );
    }

    /**
     * Start the buffer
     *
     * @action wp_loaded
     */
    public function buffer_start(): void {
        ob_start( [ $this, 'replace' ] );
    }

}

$instance = new Ngrok( network_site_url( '/' ) );

add_action( 'wp_loaded', [ $instance, 'buffer_start' ] );
// prevent infinite redirects on a multisite homepage
add_filter( 'redirect_canonical', '__return_false', -1 );
