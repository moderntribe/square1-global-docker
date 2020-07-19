<?php declare( strict_types=1 );

namespace App\Services\Migrations;

/**
 * Class Migration
 *
 * @package App\Services\Migrations
 */
abstract class Migration {

    /**
     * Run the Migration
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return bool If the migration was successful
     */
    abstract public function up( \Symfony\Component\Console\Output\OutputInterface $output ): bool;

}
