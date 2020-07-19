<?php

namespace App\Providers;

use Filebase\Database;
use Illuminate\Support\ServiceProvider;

/**
 * Init Filebase, our JSON data store.
 *
 * @codeCoverageIgnore
 *
 * @package App\Providers
 */
class JsonStoreServiceProvider extends ServiceProvider {

    public const DB_STORE = 'store';

    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     */
    public function boot() {
        $config = [
            'dir' => config( 'squareone.config-dir' ) . '/' . self::DB_STORE . '/migrations',
        ];

        $this->app->bind( 'Filebase\Database', function () use ( $config ) {
            return new Database( $config );
        } );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
    }


}
