<?php declare( strict_types=1 );

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use PragmaRX\Yaml\Package\Yaml as YamlPackage;

/**
 * Overload the Yaml package to provide the proper functionality.
 *
 * @package App\Support
 */
class Yaml extends YamlPackage {

    /**
     * Load yaml files from directory and add to Laravel config.
     *
     * @param array|string $path
     * @param string $configKey
     *
     * @return Collection
     */
    public function loadToConfig($path, $configKey): Collection {
        // @codeCoverageIgnoreStart
        if (App::configurationIsCached()) {
            return collect();
        }
        // @codeCoverageIgnoreEnd

        $loaded = [];

        if (is_array($path)) {
            foreach($path as $file) {
                if ( $this->file->isYamlFile($file) ) {
                    $loaded[] = $this->loadFile( $file ) ?? [];
                }
            }
        } else {
            $loaded[] = $this->file->isYamlFile($path) ? ( $this->loadFile($path) ?? [] ) : [];
        }

        // Properly merge Yaml configs with ability to overwrite items
        if (!empty($loaded)) {
            $loaded = array_replace_recursive( ...$loaded );
        }

        $loaded = Arr::sortRecursive($loaded);

        return $this->resolver->findAndReplaceExecutableCodeToExhaustion($loaded, $configKey);
    }
}
